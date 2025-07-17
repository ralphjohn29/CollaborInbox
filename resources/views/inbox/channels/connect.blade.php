@extends('layouts.dashboard')

@section('title', 'Connect Email Channel - CollaborInbox')

@section('body-class', 'inbox-page')

@section('page-styles')
    <style>
        .connect-container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .connect-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .connect-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .connect-subtitle {
            font-size: 1.125rem;
            color: #6b7280;
        }

        .channel-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .channel-card {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .channel-card:hover {
            border-color: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .channel-card::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, #dc2626, #ef4444);
            opacity: 0;
            z-index: -1;
            transition: opacity 0.3s ease;
            border-radius: 0.75rem;
        }

        .channel-card:hover::before {
            opacity: 0.1;
        }

        .channel-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .channel-icon img {
            max-width: 100%;
            height: auto;
        }

        .channel-gmail .channel-icon {
            background: #EA4335;
            border-radius: 0.5rem;
            padding: 1rem;
        }

        .channel-outlook .channel-icon {
            background: #0078D4;
            border-radius: 0.5rem;
            padding: 1rem;
        }

        .channel-other .channel-icon {
            background: #6b7280;
            border-radius: 0.5rem;
            padding: 1rem;
        }

        .channel-name {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .channel-description {
            font-size: 0.875rem;
            color: #6b7280;
            line-height: 1.5;
        }

        .fallback-option {
            text-align: center;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid #e5e7eb;
        }

        .fallback-link {
            color: #dc2626;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: color 0.2s ease;
        }

        .fallback-link:hover {
            color: #b91c1c;
            text-decoration: underline;
        }

        /* Modal styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 1rem;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #6b7280;
            cursor: pointer;
            transition: color 0.2s ease;
        }

        .modal-close:hover {
            color: #374151;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .coming-soon {
            text-align: center;
            padding: 2rem;
        }

        .coming-soon-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .coming-soon-text {
            font-size: 1.125rem;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }

        .coming-soon-subtext {
            font-size: 0.875rem;
            color: #9ca3af;
        }
    </style>
@endsection

@section('page-content')
    <div class="connect-container">
        <div class="connect-header">
            <h1 class="connect-title">What kind of team email do you want to connect?</h1>
            <p class="connect-subtitle">Choose your email provider to get started</p>
        </div>

        <div class="channel-grid">
            <!-- Gmail -->
            <div class="channel-card channel-gmail" onclick="handleChannelClick('gmail')">
                <div class="channel-icon">
                    <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M44 12C44 9.8 42.2 8 40 8H8C5.8 8 4 9.8 4 12V36C4 38.2 5.8 40 8 40H40C42.2 40 44 38.2 44 36V12Z" fill="white"/>
                        <path d="M44 12V36C44 38.2 42.2 40 40 40H36V16.6L24 26L12 16.6V40H8C5.8 40 4 38.2 4 36V12C4 11.4 4.18 10.84 4.48 10.36L24 24L43.52 10.36C43.82 10.84 44 11.4 44 12Z" fill="#EA4335"/>
                        <path d="M36 40V16.6L24 26V24L43.52 10.36C43.04 9.56 42.08 9 41 9H40L24 21L8 9H7C5.92 9 4.96 9.56 4.48 10.36L24 24V26L12 16.6V40H36Z" fill="white" fill-opacity="0.9"/>
                    </svg>
                </div>
                <h3 class="channel-name">Gmail</h3>
                <p class="channel-description">Connect a Gmail account to your inbox</p>
            </div>

            <!-- Microsoft Outlook -->
            <div class="channel-card channel-outlook" onclick="handleChannelClick('outlook')">
                <div class="channel-icon">
                    <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M28 4V19L44 15V8L28 4Z" fill="white"/>
                        <path d="M28 19V29L44 33V15L28 19Z" fill="white" fill-opacity="0.8"/>
                        <path d="M28 29V44L44 40V33L28 29Z" fill="white" fill-opacity="0.6"/>
                        <path d="M4 10H24V38H4V10Z" fill="white"/>
                        <path d="M14 18C10.7 18 8 20.7 8 24C8 27.3 10.7 30 14 30C17.3 30 20 27.3 20 24C20 20.7 17.3 18 14 18ZM14 27C12.3 27 11 25.7 11 24C11 22.3 12.3 21 14 21C15.7 21 17 22.3 17 24C17 25.7 15.7 27 14 27Z" fill="#0078D4"/>
                    </svg>
                </div>
                <h3 class="channel-name">Microsoft Outlook</h3>
                <p class="channel-description">Connect a Microsoft Outlook account to your inbox</p>
            </div>

            <!-- Other Mail Account -->
            <div class="channel-card channel-other" onclick="window.location.href='{{ route('inbox.channels.other') }}'">
                <div class="channel-icon">
                    <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8 10C8 8.89543 8.89543 8 10 8H38C39.1046 8 40 8.89543 40 10V38C40 39.1046 39.1046 40 38 40H10C8.89543 40 8 39.1046 8 38V10Z" stroke="white" stroke-width="2"/>
                        <path d="M8 16L24 26L40 16" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="24" cy="26" r="2" fill="white"/>
                    </svg>
                </div>
                <h3 class="channel-name">Other mail account</h3>
                <p class="channel-description">Connect other accounts by setting up email forwarding</p>
            </div>
        </div>

        <div class="fallback-option">
            <p style="color: #6b7280; margin-bottom: 0.5rem;">Looking for HubSpot CRM integration?</p>
            <a href="#" class="fallback-link" onclick="showComingSoon('hubspot'); return false;">
                I'll use my HubSpot fallback email
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 8H13M13 8L8 3M13 8L8 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        </div>
    </div>

    <!-- Coming Soon Modal -->
    <div class="modal-overlay" id="comingSoonModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Coming Soon</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="coming-soon">
                    <div class="coming-soon-icon">🚀</div>
                    <p class="coming-soon-text" id="modalMessage">This feature is coming soon!</p>
                    <p class="coming-soon-subtext">We're working hard to bring you this integration. Stay tuned!</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-scripts')
<script>
    function handleChannelClick(channel) {
        if (channel === 'gmail') {
            showComingSoon('Gmail');
        } else if (channel === 'outlook') {
            showComingSoon('Microsoft Outlook');
        }
    }

    function showComingSoon(provider) {
        const modal = document.getElementById('comingSoonModal');
        const title = document.getElementById('modalTitle');
        const message = document.getElementById('modalMessage');
        
        if (provider === 'hubspot') {
            title.textContent = 'HubSpot Integration';
            message.textContent = 'HubSpot CRM integration is coming soon!';
        } else {
            title.textContent = provider + ' Integration';
            message.textContent = provider + ' OAuth integration is coming soon!';
        }
        
        modal.classList.add('active');
    }

    function closeModal() {
        document.getElementById('comingSoonModal').classList.remove('active');
    }

    // Close modal when clicking outside
    document.getElementById('comingSoonModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
</script>
@endsection
