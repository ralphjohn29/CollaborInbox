@extends('layouts.app')

@section('title', 'Login - CollaborInbox')

@section('body-class', 'login-page')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <style>
        .emergency-logout {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            font-size: 12px;
            cursor: pointer;
            z-index: 1000;
        }
        .emergency-logout:hover {
            background-color: #e9ecef;
        }
    </style>
@endsection

@section('content')
    <div class="container">
        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="login-container">
            <div class="login-form">
                <div class="login-header">
                    <h1>CollaborInbox</h1>
                    <p>Please log in to your account</p>
                </div>
                
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus />
                        @error('email')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required autocomplete="current-password" />
                        @error('password')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group remember-me">
                        <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }} />
                        <label for="remember">Remember me</label>
                    </div>
                    
                    <button type="submit" class="primary-button">
                        Log In
                    </button>
                </form>
                
                <div class="login-footer">
                    <p class="help-text">Having trouble logging in? Contact your administrator.</p>
                    <p class="signup-link">Don't have an account? <a href="{{ route('signup') }}">Sign up</a></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="emergency-logout" id="emergency-logout">
        Emergency Logout (Stuck in login loop?)
    </div>
@endsection

@section('scripts')
    <script>
        // Emergency logout function
        document.getElementById('emergency-logout').addEventListener('click', function() {
            // Clear any session cookies
            document.cookie.split(";").forEach(function(c) {
                document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/");
            });
            
            // Force reload from server, not cache
            window.location.href = '/login?nocache=' + new Date().getTime();
            
            alert('Authentication data cleared! Page will reload.');
        });
    </script>
@endsection 