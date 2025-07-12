@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">Connect Your Email Account</h1>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <form id="email-setup-form" method="POST" action="{{ route('inbox.settings.accounts.store') }}">
                @csrf
                
                <!-- Step 1: Email Address -->
                <div class="mb-6">
                    <label for="email_address" class="block text-sm font-medium text-gray-700 mb-2">
                        Email Address
                    </label>
                    <input type="email" 
                           id="email_address" 
                           name="email_address" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="your-email@gmail.com"
                           required>
                    <p class="mt-1 text-sm text-gray-500">
                        Supports Gmail, Outlook, Yahoo, and most email providers
                    </p>
                </div>
                
                <!-- Provider Info (shown after email input) -->
                <div id="provider-info" class="hidden mb-6 p-4 bg-blue-50 rounded-md">
                    <div class="flex items-start">
                        <svg class="h-5 w-5 text-blue-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800" id="provider-name"></h3>
                            <p class="mt-1 text-sm text-blue-700" id="provider-instructions"></p>
                        </div>
                    </div>
                </div>
                
                <!-- Step 2: Authentication -->
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password / App Password
                    </label>
                    <input type="password" 
                           id="password" 
                           name="incoming_server_password" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                    <p class="mt-1 text-sm text-gray-500" id="password-help">
                        Enter your email password
                    </p>
                </div>
                
                <!-- Advanced Settings (collapsible) -->
                <div class="mb-6">
                    <button type="button" 
                            id="toggle-advanced" 
                            class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                        Advanced Settings ▼
                    </button>
                    
                    <div id="advanced-settings" class="hidden mt-4 space-y-4">
                        <!-- IMAP Settings -->
                        <div class="border-t pt-4">
                            <h3 class="text-sm font-medium text-gray-700 mb-3">Incoming Mail (IMAP) Settings</h3>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">IMAP Server</label>
                                    <input type="text" 
                                           name="incoming_server_host" 
                                           id="imap_host"
                                           class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Port</label>
                                    <input type="number" 
                                           name="incoming_server_port" 
                                           id="imap_port"
                                           class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md">
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                           name="incoming_server_ssl" 
                                           id="imap_ssl"
                                           value="1"
                                           checked
                                           class="rounded border-gray-300 text-blue-600">
                                    <span class="ml-2 text-sm text-gray-700">Use SSL/TLS</span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- SMTP Settings -->
                        <div class="border-t pt-4">
                            <h3 class="text-sm font-medium text-gray-700 mb-3">Outgoing Mail (SMTP) Settings</h3>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">SMTP Server</label>
                                    <input type="text" 
                                           name="outgoing_server_host" 
                                           id="smtp_host"
                                           class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Port</label>
                                    <input type="number" 
                                           name="outgoing_server_port" 
                                           id="smtp_port"
                                           class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md">
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                           name="outgoing_server_ssl" 
                                           id="smtp_ssl"
                                           value="1"
                                           checked
                                           class="rounded border-gray-300 text-blue-600">
                                    <span class="ml-2 text-sm text-gray-700">Use SSL/TLS</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Test Connection Button -->
                <div class="mb-6">
                    <button type="button" 
                            id="test-connection" 
                            class="w-full px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
                        Test Connection
                    </button>
                    
                    <div id="test-result" class="mt-3 hidden">
                        <!-- Test results will be shown here -->
                    </div>
                </div>
                
                <!-- Submit Button -->
                <div>
                    <button type="submit" 
                            id="submit-button"
                            class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                            disabled>
                        Connect Email Account
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Popular Providers Quick Setup -->
        <div class="mt-8">
            <h2 class="text-lg font-semibold mb-4">Quick Setup Guides</h2>
            
            <div class="grid grid-cols-2 gap-4">
                <a href="https://support.google.com/accounts/answer/185833" 
                   target="_blank"
                   class="flex items-center p-4 bg-white rounded-lg shadow hover:shadow-md transition">
                    <img src="https://www.google.com/gmail/about/static/images/logo-gmail.png" 
                         alt="Gmail" 
                         class="w-8 h-8 mr-3">
                    <div>
                        <h3 class="font-medium">Gmail</h3>
                        <p class="text-sm text-gray-600">Create App Password</p>
                    </div>
                </a>
                
                <a href="https://support.microsoft.com/en-us/account-billing/using-app-passwords-with-apps-that-don-t-support-two-step-verification-5896ed9b-4263-e681-128a-a6f2979a7944" 
                   target="_blank"
                   class="flex items-center p-4 bg-white rounded-lg shadow hover:shadow-md transition">
                    <img src="https://www.microsoft.com/favicon.ico" 
                         alt="Outlook" 
                         class="w-8 h-8 mr-3">
                    <div>
                        <h3 class="font-medium">Outlook</h3>
                        <p class="text-sm text-gray-600">App Password Guide</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const emailInput = document.getElementById('email_address');
    const providerInfo = document.getElementById('provider-info');
    const providerName = document.getElementById('provider-name');
    const providerInstructions = document.getElementById('provider-instructions');
    const passwordHelp = document.getElementById('password-help');
    const testButton = document.getElementById('test-connection');
    const submitButton = document.getElementById('submit-button');
    const testResult = document.getElementById('test-result');
    const toggleAdvanced = document.getElementById('toggle-advanced');
    const advancedSettings = document.getElementById('advanced-settings');
    
    // Toggle advanced settings
    toggleAdvanced.addEventListener('click', function() {
        advancedSettings.classList.toggle('hidden');
        this.textContent = advancedSettings.classList.contains('hidden') 
            ? 'Advanced Settings ▼' 
            : 'Advanced Settings ▲';
    });
    
    // Auto-detect email provider
    emailInput.addEventListener('blur', function() {
        const email = this.value;
        if (!email) return;
        
        // Set username fields
        document.querySelector('input[name="incoming_server_username"]')?.remove();
        const usernameInput = document.createElement('input');
        usernameInput.type = 'hidden';
        usernameInput.name = 'incoming_server_username';
        usernameInput.value = email;
        this.form.appendChild(usernameInput);
        
        // Detect provider
        fetch('/api/email/detect-provider', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ email: email })
        })
        .then(response => response.json())
        .then(data => {
            if (data.provider) {
                // Show provider info
                providerInfo.classList.remove('hidden');
                providerName.textContent = data.provider.name;
                
                if (data.provider.app_password_required) {
                    providerInstructions.textContent = data.provider.instructions;
                    passwordHelp.textContent = 'App Password required for ' + data.provider.name;
                }
                
                // Auto-fill server settings
                if (data.provider.imap) {
                    document.getElementById('imap_host').value = data.provider.imap.host;
                    document.getElementById('imap_port').value = data.provider.imap.port;
                    document.getElementById('imap_ssl').checked = data.provider.imap.encryption === 'ssl';
                }
                
                if (data.provider.smtp) {
                    document.getElementById('smtp_host').value = data.provider.smtp.host;
                    document.getElementById('smtp_port').value = data.provider.smtp.port;
                    document.getElementById('smtp_ssl').checked = data.provider.smtp.encryption === 'tls';
                }
            }
        });
    });
    
    // Test connection
    testButton.addEventListener('click', function() {
        const form = document.getElementById('email-setup-form');
        const formData = new FormData(form);
        
        testButton.disabled = true;
        testButton.textContent = 'Testing...';
        testResult.classList.add('hidden');
        
        fetch('/api/email/test-connection', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            testResult.classList.remove('hidden');
            
            if (data.success) {
                testResult.innerHTML = `
                    <div class="p-3 bg-green-100 text-green-700 rounded-md">
                        ✓ Connection successful! You can now save this email account.
                    </div>
                `;
                submitButton.disabled = false;
            } else {
                testResult.innerHTML = `
                    <div class="p-3 bg-red-100 text-red-700 rounded-md">
                        ✗ Connection failed: ${data.error}
                    </div>
                `;
                submitButton.disabled = true;
            }
        })
        .finally(() => {
            testButton.disabled = false;
            testButton.textContent = 'Test Connection';
        });
    });
});
</script>
@endsection
