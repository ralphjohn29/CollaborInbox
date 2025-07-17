@extends('layouts.dashboard')

@section('title', 'Connect Other Email Account - CollaborInbox')

@section('body-class', 'inbox-page')

@section('page-styles')
    <style>
        .setup-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        /* Progress Steps */
        .progress-header {
            background: white;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .progress-steps {
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
        }

        .progress-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
            position: relative;
            z-index: 1;
        }

        .progress-step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e5e7eb;
            color: #6b7280;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }

        .progress-step.active .progress-step-number {
            background: #dc2626;
            color: white;
        }

        .progress-step.completed .progress-step-number {
            background: #10b981;
            color: white;
        }

        .progress-step-label {
            font-size: 0.875rem;
            color: #6b7280;
            text-align: center;
        }

        .progress-step.active .progress-step-label {
            color: #1f2937;
            font-weight: 500;
        }

        .progress-line {
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e5e7eb;
            z-index: 0;
        }

        .progress-line-fill {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            background: #10b981;
            transition: width 0.3s ease;
        }

        /* Form Sections */
        .setup-form {
            background: white;
            border-radius: 0.75rem;
            padding: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .form-step {
            display: none;
        }

        .form-step.active {
            display: block;
        }

        .step-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 1rem;
        }

        .step-description {
            color: #6b7280;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }

        .form-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }

        .form-help {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        @media (max-width: 640px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        /* Copy Field */
        .copy-field {
            position: relative;
            display: flex;
            align-items: center;
        }

        .copy-field input {
            padding-right: 3rem;
        }

        .copy-button {
            position: absolute;
            right: 0.5rem;
            padding: 0.5rem 0.75rem;
            background: #dc2626;
            color: white;
            border: none;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .copy-button:hover {
            background: #b91c1c;
        }

        .copy-button.copied {
            background: #10b981;
        }

        /* Instructions Box */
        .instructions-box {
            background: #fef3c7;
            border: 1px solid #fde68a;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .instructions-title {
            font-weight: 600;
            color: #92400e;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .instructions-list {
            list-style: decimal;
            padding-left: 1.5rem;
            color: #92400e;
            font-size: 0.875rem;
            line-height: 1.6;
        }

        /* Navigation Buttons */
        .form-navigation {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e5e7eb;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-secondary {
            background: white;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .btn-secondary:hover {
            background: #f9fafb;
        }

        .btn-primary {
            background: #dc2626;
            color: white;
        }

        .btn-primary:hover {
            background: #b91c1c;
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Test Connection */
        .test-section {
            margin-top: 1.5rem;
            padding: 1rem;
            background: #f9fafb;
            border-radius: 0.5rem;
        }

        .test-result {
            margin-top: 1rem;
            padding: 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            display: none;
        }

        .test-result.success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .test-result.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        /* Loading Spinner */
        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            border-top-color: currentColor;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Success State */
        .success-message {
            text-align: center;
            padding: 3rem;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: #d1fae5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .success-icon svg {
            width: 40px;
            height: 40px;
            color: #10b981;
        }
    </style>
@endsection

@section('page-content')
    <div class="setup-container">
        <!-- Progress Header -->
        <div class="progress-header">
            <div class="progress-steps">
                <div class="progress-line">
                    <div class="progress-line-fill" id="progressFill" style="width: 0%;"></div>
                </div>
                <div class="progress-step active" id="step1-indicator">
                    <div class="progress-step-number">1</div>
                    <div class="progress-step-label">Email details</div>
                </div>
                <div class="progress-step" id="step2-indicator">
                    <div class="progress-step-number">2</div>
                    <div class="progress-step-label">Channel automation</div>
                </div>
                <div class="progress-step" id="step3-indicator">
                    <div class="progress-step-number">3</div>
                    <div class="progress-step-label">Connect</div>
                </div>
            </div>
        </div>

        <!-- Setup Form -->
        <form id="emailSetupForm" class="setup-form" method="POST" action="{{ route('inbox.channels.other.store') }}">
            @csrf
            
            <!-- Step 1: Email Details -->
            <div class="form-step active" id="step1">
                <h2 class="step-title">Connect your email account</h2>
                <p class="step-description">Take the final step in connecting your shared email account to the Inbox. Follow the instructions for your email provider to start getting emails in HubSpot.</p>

                <div class="instructions-box">
                    <div class="instructions-title">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10 18C14.4183 18 18 14.4183 18 10C18 5.58172 14.4183 2 10 2C5.58172 2 2 5.58172 2 10C2 14.4183 5.58172 18 10 18Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M10 14L10 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="10" cy="6" r="1" fill="currentColor"/>
                        </svg>
                        Copy your forwarding address
                    </div>
                    <div class="copy-field">
                        <input type="text" id="forwardingAddress" value="hello-447@243282747.nd2.hubspot-inbox.com" readonly class="form-input">
                        <button type="button" class="copy-button" onclick="copyToClipboard()">Copy</button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Add it to your email account</label>
                    <p class="form-help">Select a provider to view instructions for how to add your forwarding address.</p>
                    <select name="email_provider" id="emailProvider" class="form-input form-select" onchange="updateInstructions()">
                        <option value="">Select an email provider</option>
                        <option value="gmail">Gmail</option>
                        <option value="outlook">Outlook</option>
                        <option value="yahoo">Yahoo Mail</option>
                        <option value="icloud">iCloud Mail</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div id="providerInstructions" style="display: none;">
                    <!-- Instructions will be dynamically inserted here -->
                </div>

                <div class="form-group">
                    <label class="form-label" for="email_address">Email Address</label>
                    <input type="email" name="email_address" id="email_address" class="form-input" placeholder="team@company.com" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="from_name">Display Name</label>
                    <input type="text" name="from_name" id="from_name" class="form-input" placeholder="Company Support">
                    <p class="form-help">This name will appear when sending emails from this account</p>
                </div>
            </div>

            <!-- Step 2: Channel Automation -->
            <div class="form-step" id="step2">
                <h2 class="step-title">Take action on your conversations</h2>
                <p class="step-description">Automatically assign conversations or tag incoming conversations as tickets.</p>

                <div class="form-group">
                    <label class="form-label">
                        <input type="checkbox" name="auto_assign" value="1">
                        Automatically assign conversations
                    </label>
                    <p class="form-help">Automatically assign conversations to specific team members</p>
                </div>

                <div id="assignmentOptions" style="display: none; margin-left: 1.5rem; margin-top: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="assign_to">Assign to</label>
                        <select name="assign_to" id="assign_to" class="form-input form-select">
                            <option value="">Select team member</option>
                            @foreach($users ?? [] as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 2rem;">
                    <label class="form-label">
                        <input type="checkbox" name="create_tickets" value="1">
                        Create tickets for incoming emails
                    </label>
                    <p class="form-help">Automatically create support tickets from incoming emails</p>
                </div>
            </div>

            <!-- Step 3: Connect -->
            <div class="form-step" id="step3">
                <h2 class="step-title">Customize your email details</h2>
                <p class="step-description">Set the information contacts will see when they receive an email from you.</p>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="incoming_server_host">IMAP Server</label>
                        <input type="text" name="incoming_server_host" id="incoming_server_host" class="form-input" placeholder="imap.gmail.com" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="incoming_server_port">IMAP Port</label>
                        <input type="number" name="incoming_server_port" id="incoming_server_port" class="form-input" value="993" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="incoming_server_encryption">IMAP Encryption</label>
                    <select name="incoming_server_encryption" id="incoming_server_encryption" class="form-input form-select" required>
                        <option value="ssl" selected>SSL</option>
                        <option value="tls">TLS</option>
                        <option value="none">None</option>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="outgoing_server_host">SMTP Server</label>
                        <input type="text" name="outgoing_server_host" id="outgoing_server_host" class="form-input" placeholder="smtp.gmail.com" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="outgoing_server_port">SMTP Port</label>
                        <input type="number" name="outgoing_server_port" id="outgoing_server_port" class="form-input" value="587" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="outgoing_server_encryption">SMTP Encryption</label>
                    <select name="outgoing_server_encryption" id="outgoing_server_encryption" class="form-input form-select" required>
                        <option value="tls" selected>TLS</option>
                        <option value="ssl">SSL</option>
                        <option value="none">None</option>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="username">Username</label>
                        <input type="text" name="incoming_server_username" id="username" class="form-input" placeholder="team@company.com" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <input type="password" name="incoming_server_password" id="password" class="form-input" required>
                    </div>
                </div>

                <input type="hidden" name="outgoing_server_username" id="outgoing_server_username">
                <input type="hidden" name="outgoing_server_password" id="outgoing_server_password">

                <div class="test-section">
                    <button type="button" class="btn btn-secondary" onclick="testConnection()">
                        <span id="testButtonText">Test Connection</span>
                    </button>
                    <div id="testResult" class="test-result"></div>
                </div>

                <div class="form-group" style="margin-top: 2rem;">
                    <label class="form-label" for="team_signature">Team Signature (optional)</label>
                    <textarea name="team_signature" id="team_signature" rows="4" class="form-input" placeholder="Best regards,&#10;The Support Team"></textarea>
                </div>
            </div>

            <!-- Navigation -->
            <div class="form-navigation">
                <button type="button" class="btn btn-secondary" id="backBtn" onclick="previousStep()" style="display: none;">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 12L6 8L10 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Back
                </button>
                <span></span>
                <button type="button" class="btn btn-primary" id="nextBtn" onclick="nextStep()">
                    Next
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 4L10 8L6 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <button type="submit" class="btn btn-success" id="submitBtn" style="display: none;">
                    Connect & finish
                </button>
            </div>
        </form>
    </div>
@endsection

@section('page-scripts')
<script>
    let currentStep = 1;
    const totalSteps = 3;

    function updateProgress() {
        const progressPercentage = ((currentStep - 1) / (totalSteps - 1)) * 100;
        document.getElementById('progressFill').style.width = progressPercentage + '%';

        // Update step indicators
        for (let i = 1; i <= totalSteps; i++) {
            const indicator = document.getElementById(`step${i}-indicator`);
            if (i < currentStep) {
                indicator.classList.add('completed');
                indicator.classList.remove('active');
            } else if (i === currentStep) {
                indicator.classList.add('active');
                indicator.classList.remove('completed');
            } else {
                indicator.classList.remove('active', 'completed');
            }
        }

        // Update navigation buttons
        document.getElementById('backBtn').style.display = currentStep > 1 ? 'inline-flex' : 'none';
        document.getElementById('nextBtn').style.display = currentStep < totalSteps ? 'inline-flex' : 'none';
        document.getElementById('submitBtn').style.display = currentStep === totalSteps ? 'inline-flex' : 'none';
    }

    function showStep(step) {
        document.querySelectorAll('.form-step').forEach(el => {
            el.classList.remove('active');
        });
        document.getElementById(`step${step}`).classList.add('active');
    }

    function nextStep() {
        if (validateStep(currentStep)) {
            if (currentStep < totalSteps) {
                currentStep++;
                showStep(currentStep);
                updateProgress();
            }
        }
    }

    function previousStep() {
        if (currentStep > 1) {
            currentStep--;
            showStep(currentStep);
            updateProgress();
        }
    }

    function validateStep(step) {
        switch(step) {
            case 1:
                const email = document.getElementById('email_address').value;
                if (!email) {
                    alert('Please enter an email address');
                    return false;
                }
                return true;
            case 2:
                return true;
            case 3:
                // Validate connection details
                const fields = ['incoming_server_host', 'incoming_server_port', 'outgoing_server_host', 'outgoing_server_port', 'username', 'password'];
                for (let field of fields) {
                    if (!document.getElementById(field).value) {
                        alert('Please fill in all connection details');
                        return false;
                    }
                }
                return true;
        }
        return true;
    }

    function copyToClipboard() {
        const input = document.getElementById('forwardingAddress');
        input.select();
        document.execCommand('copy');
        
        const button = event.target;
        button.textContent = 'Copied!';
        button.classList.add('copied');
        
        setTimeout(() => {
            button.textContent = 'Copy';
            button.classList.remove('copied');
        }, 2000);
    }

    function updateInstructions() {
        const provider = document.getElementById('emailProvider').value;
        const instructionsDiv = document.getElementById('providerInstructions');
        
        if (provider) {
            instructionsDiv.style.display = 'block';
            instructionsDiv.innerHTML = getInstructions(provider);
        } else {
            instructionsDiv.style.display = 'none';
        }
    }

    function getInstructions(provider) {
        const instructions = {
            gmail: `
                <div class="instructions-box">
                    <div class="instructions-title">Gmail Instructions</div>
                    <ol class="instructions-list">
                        <li>Open Gmail and click the gear icon → "See all settings"</li>
                        <li>Click "Forwarding and POP/IMAP" tab</li>
                        <li>Click "Add a forwarding address"</li>
                        <li>Paste the forwarding address and click "Next"</li>
                        <li>Confirm the forwarding request</li>
                        <li>Enable "Forward a copy of incoming mail to" and select the address</li>
                    </ol>
                </div>
            `,
            outlook: `
                <div class="instructions-box">
                    <div class="instructions-title">Outlook Instructions</div>
                    <ol class="instructions-list">
                        <li>Sign in to Outlook.com</li>
                        <li>Click Settings → View all Outlook settings</li>
                        <li>Select Mail → Forwarding</li>
                        <li>Check "Enable forwarding"</li>
                        <li>Enter the forwarding address</li>
                        <li>Click "Save"</li>
                    </ol>
                </div>
            `,
            other: `
                <div class="instructions-box">
                    <div class="instructions-title">General Instructions</div>
                    <ol class="instructions-list">
                        <li>Log in to your email provider</li>
                        <li>Navigate to email settings or preferences</li>
                        <li>Look for "Forwarding" or "Email forwarding" options</li>
                        <li>Add the forwarding address provided above</li>
                        <li>Save your settings</li>
                    </ol>
                </div>
            `
        };
        
        return instructions[provider] || instructions.other;
    }

    function testConnection() {
        const button = document.getElementById('testButtonText');
        const resultDiv = document.getElementById('testResult');
        
        // Show loading state
        button.innerHTML = '<span class="spinner"></span> Testing...';
        resultDiv.style.display = 'none';
        
        // Gather connection data
        const data = {
            provider: 'other',
            email_address: document.getElementById('email_address').value,
            incoming_server_host: document.getElementById('incoming_server_host').value,
            incoming_server_port: document.getElementById('incoming_server_port').value,
            incoming_server_encryption: document.getElementById('incoming_server_encryption').value,
            incoming_server_username: document.getElementById('username').value,
            incoming_server_password: document.getElementById('password').value,
        };
        
        // Test connection
        fetch('{{ route('inbox.channels.test') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            button.textContent = 'Test Connection';
            resultDiv.style.display = 'block';
            
            if (result.success) {
                resultDiv.className = 'test-result success';
                resultDiv.innerHTML = '✓ ' + result.message;
            } else {
                resultDiv.className = 'test-result error';
                resultDiv.innerHTML = '✗ ' + result.message;
            }
        })
        .catch(error => {
            button.textContent = 'Test Connection';
            resultDiv.style.display = 'block';
            resultDiv.className = 'test-result error';
            resultDiv.innerHTML = '✗ Connection test failed. Please check your settings.';
        });
    }

    // Handle checkbox for auto-assign
    document.querySelector('input[name="auto_assign"]').addEventListener('change', function() {
        document.getElementById('assignmentOptions').style.display = this.checked ? 'block' : 'none';
    });

    // Copy username to SMTP username
    document.getElementById('username').addEventListener('input', function() {
        document.getElementById('outgoing_server_username').value = this.value;
    });

    // Copy password to SMTP password
    document.getElementById('password').addEventListener('input', function() {
        document.getElementById('outgoing_server_password').value = this.value;
    });

    // Initialize
    updateProgress();
</script>
@endsection
