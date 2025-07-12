<header class="header">
    <button class="header-toggle" onclick="toggleSidebar()">
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M3 5H17M3 10H17M3 15H17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </button>

    <button class="header-toggle mobile-sidebar-toggle" style="display: none;">
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M3 5H17M3 10H17M3 15H17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </button>

    <div class="header-search">
        <input type="text" class="search-input" placeholder="Search..." id="globalSearch">
    </div>

    <div class="header-actions">
        <button class="header-button" title="Notifications">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M15 7C15 5.67392 14.4732 4.40215 13.5355 3.46447C12.5979 2.52678 11.3261 2 10 2C8.67392 2 7.40215 2.52678 6.46447 3.46447C5.52678 4.40215 5 5.67392 5 7C5 13 2 15 2 15H18C18 15 15 13 15 7Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M11.73 18C11.5542 18.3031 11.3019 18.5547 10.9982 18.7295C10.6946 18.9044 10.3504 18.9965 10 18.9965C9.64964 18.9965 9.30541 18.9044 9.00179 18.7295C8.69818 18.5547 8.44583 18.3031 8.27 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span class="notification-badge"></span>
        </button>

        <button class="header-button" title="Help">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="2"/>
                <path d="M7.88 7.12C8.07393 6.56539 8.45181 6.09545 8.95251 5.78666C9.4532 5.47788 10.0472 5.34847 10.6397 5.42006C11.2321 5.49166 11.7882 5.76041 12.2204 6.18524C12.6525 6.61007 12.9362 7.16730 13.0265 7.77177C13.1168 8.37624 13.0083 8.99438 12.7174 9.53207C12.4265 10.0698 11.9686 10.4987 11.413 10.7551C10.8574 11.0116 10.2334 11.0821 9.63302 10.9564C9.03267 10.8308 8.48755 10.5157 8.08 10.06" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="10" cy="14.5" r="0.5" fill="currentColor"/>
            </svg>
        </button>

        <div class="dropdown">
            <div class="user-menu" onclick="toggleDropdown('headerUserDropdown')">
                <div class="user-avatar">
                    {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                </div>
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="dropdown-menu" id="headerUserDropdown">
                <div style="padding: 0.75rem; border-bottom: 1px solid hsl(var(--border));">
                    <div style="font-weight: 500; color: hsl(var(--foreground));">{{ Auth::user()->name ?? 'User' }}</div>
                    <div style="font-size: 0.75rem; color: hsl(var(--muted-foreground)); margin-top: 0.25rem;">{{ Auth::user()->email ?? '' }}</div>
                </div>
                <a href="#" class="dropdown-item">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right: 0.5rem;">
                        <circle cx="8" cy="5" r="3" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M3 14C3 12.3431 4.34315 11 6 11H10C11.6569 11 13 12.3431 13 14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    Profile Settings
                </a>
                <a href="{{ route('inbox.settings.dispositions') }}" class="dropdown-item">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right: 0.5rem;">
                        <circle cx="8" cy="8" r="2" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M8 1V3M8 13V15M15 8H13M3 8H1M12.728 12.728L11.314 11.314M4.686 4.686L3.272 3.272M12.728 3.272L11.314 4.686M4.686 11.314L3.272 12.728" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    Settings
                </a>
                <div class="dropdown-divider"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item" style="width: 100%; text-align: left; border: none; background: none;">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right: 0.5rem;">
                            <path d="M6 14H3C2.44772 14 2 13.5523 2 13V3C2 2.44772 2.44772 2 3 2H6M11 11L14 8M14 8L11 5M14 8H6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

<style>
@media (max-width: 768px) {
    .mobile-sidebar-toggle {
        display: block !important;
    }
    .header-toggle:not(.mobile-sidebar-toggle) {
        display: none !important;
    }
}
</style>
