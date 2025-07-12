@extends('layouts.app')

@section('title', isset($disposition) ? 'Edit Disposition' : 'Add Disposition')

@section('body-class', 'inbox-settings-page')

@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Reuse styles from accounts page */
        :root {
            --background: 0 0% 100%;
            --foreground: 222.2 84% 4.9%;
            --card: 0 0% 100%;
            --card-foreground: 222.2 84% 4.9%;
            --popover: 0 0% 100%;
            --popover-foreground: 222.2 84% 4.9%;
            --primary: 222.2 47.4% 11.2%;
            --primary-foreground: 210 40% 98%;
            --secondary: 210 40% 96.1%;
            --secondary-foreground: 222.2 47.4% 11.2%;
            --muted: 210 40% 96.1%;
            --muted-foreground: 215.4 16.3% 46.9%;
            --accent: 210 40% 96.1%;
            --accent-foreground: 222.2 47.4% 11.2%;
            --destructive: 0 84.2% 60.2%;
            --destructive-foreground: 210 40% 98%;
            --border: 214.3 31.8% 91.4%;
            --input: 214.3 31.8% 91.4%;
            --ring: 222.2 84% 4.9%;
            --radius: 0.5rem;
        }

        * {
            box-sizing: border-box;
        }

        body.inbox-settings-page {
            margin: 0 !important;
            padding: 0 !important;
            background-color: #fafafa !important;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', sans-serif !important;
        }

        .settings-container {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* Sidebar styles */
        .sidebar {
            width: 240px;
            background-color: hsl(var(--card));
            border-right: 1px solid hsl(var(--border));
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            padding: 1.5rem 1rem;
            border-bottom: 1px solid hsl(var(--border));
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: hsl(var(--foreground));
        }

        .sidebar-logo-icon {
            width: 32px;
            height: 32px;
            background-color: hsl(var(--primary));
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            color: hsl(var(--primary-foreground));
            font-weight: 600;
            flex-shrink: 0;
        }

        .sidebar-logo-text {
            font-weight: 600;
            font-size: 1.125rem;
        }

        .sidebar-nav {
            flex: 1;
            padding: 1rem 0;
            overflow-y: auto;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 0.625rem 1rem;
            margin: 0 0.5rem 0.25rem;
            border-radius: calc(var(--radius) - 2px);
            text-decoration: none;
            color: hsl(var(--muted-foreground));
            transition: all 0.2s ease;
            gap: 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .nav-item:hover {
            background-color: hsl(var(--accent));
            color: hsl(var(--accent-foreground));
        }

        .nav-item.active {
            background-color: hsl(var(--secondary));
            color: hsl(var(--foreground));
        }

        .nav-item-icon {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        /* Main content */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .header {
            height: 60px;
            background-color: hsl(var(--card));
            border-bottom: 1px solid hsl(var(--border));
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            gap: 1rem;
        }

        .content {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
            background-color: hsl(var(--background));
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.875rem;
            font-weight: 700;
            color: hsl(var(--foreground));
            margin: 0 0 0.5rem 0;
        }

        .page-description {
            color: hsl(var(--muted-foreground));
            font-size: 0.875rem;
        }

        /* Form styles */
        .form-container {
            background-color: hsl(var(--card));
            border: 1px solid hsl(var(--border));
            border-radius: var(--radius);
            padding: 2rem;
            max-width: 600px;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: hsl(var(--foreground));
        }

        .form-input {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid hsl(var(--border));
            border-radius: calc(var(--radius) - 2px);
            background-color: hsl(var(--background));
            font-size: 0.875rem;
            outline: none;
            transition: border-color 0.2s ease;
            font-family: inherit;
        }

        .form-input:focus {
            border-color: hsl(var(--primary));
        }

        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-help {
            font-size: 0.813rem;
            color: hsl(var(--muted-foreground));
            margin-top: 0.25rem;
        }

        .form-error {
            font-size: 0.813rem;
            color: hsl(var(--destructive));
            margin-top: 0.25rem;
        }

        /* Button styles */
        .btn {
            padding: 0.5rem 1rem;
            border: 1px solid transparent;
            border-radius: calc(var(--radius) - 2px);
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-primary {
            background-color: hsl(var(--primary));
            color: hsl(var(--primary-foreground));
        }

        .btn-primary:hover {
            background-color: hsl(var(--primary) / 0.9);
        }

        .btn-outline {
            border-color: hsl(var(--border));
            background-color: transparent;
            color: hsl(var(--foreground));
        }

        .btn-outline:hover {
            background-color: hsl(var(--accent));
            color: hsl(var(--accent-foreground));
        }

        .form-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 2rem;
        }

        /* Color picker */
        .color-picker-group {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .color-input {
            width: 80px;
            height: 40px;
            padding: 0.25rem;
            border: 1px solid hsl(var(--border));
            border-radius: calc(var(--radius) - 2px);
            cursor: pointer;
        }

        .color-preview {
            width: 40px;
            height: 40px;
            border: 1px solid hsl(var(--border));
            border-radius: calc(var(--radius) - 2px);
        }

        /* Preset colors */
        .preset-colors {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.5rem;
            flex-wrap: wrap;
        }

        .preset-color {
            width: 32px;
            height: 32px;
            border-radius: 4px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.2s ease;
        }

        .preset-color:hover {
            transform: scale(1.1);
            border-color: hsl(var(--primary));
        }

        .preset-color.selected {
            border-color: hsl(var(--primary));
            box-shadow: 0 0 0 2px hsl(var(--primary) / 0.2);
        }
    </style>
@endsection

@section('content')
<div class="settings-container">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="{{ url('/dashboard') }}" class="sidebar-logo">
                <div class="sidebar-logo-icon">CI</div>
                <span class="sidebar-logo-text">CollaborInbox</span>
            </a>
        </div>
        
        <nav class="sidebar-nav">
            <a href="{{ url('/dashboard') }}" class="nav-item">
                <span class="nav-item-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                </span>
                <span class="nav-item-text">Dashboard</span>
            </a>
            
            <a href="{{ url('/inbox') }}" class="nav-item">
                <span class="nav-item-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                </span>
                <span class="nav-item-text">Inbox</span>
            </a>

            <div style="margin-top: 2rem; padding: 0 1rem;">
                <div style="font-size: 0.75rem; color: hsl(var(--muted-foreground)); font-weight: 600; margin-bottom: 0.5rem;">
                    SETTINGS
                </div>
                <a href="{{ route('inbox.settings.accounts') }}" class="nav-item">
                    <span class="nav-item-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M8 12h8"/>
                            <path d="M12 8v8"/>
                        </svg>
                    </span>
                    <span class="nav-item-text">Email Accounts</span>
                </a>
                
                <a href="{{ route('inbox.settings.dispositions') }}" class="nav-item active">
                    <span class="nav-item-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                            <line x1="12" y1="22.08" x2="12" y2="12"/>
                        </svg>
                    </span>
                    <span class="nav-item-text">Dispositions</span>
                </a>
            </div>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <header class="header">
            <h1 style="font-size: 1.25rem; font-weight: 600; margin: 0;">
                {{ isset($disposition) ? 'Edit Disposition' : 'Add Disposition' }}
            </h1>
        </header>

        <!-- Content -->
        <div class="content">
            <div class="page-header">
                <h1 class="page-title">{{ isset($disposition) ? 'Edit Disposition' : 'Add Disposition' }}</h1>
                <p class="page-description">
                    {{ isset($disposition) ? 'Update the disposition details' : 'Create a new disposition to categorize emails' }}
                </p>
            </div>

            @if($errors->any())
                <div style="padding: 1rem; background-color: hsl(var(--destructive) / 0.1); color: hsl(var(--destructive)); border-radius: var(--radius); margin-bottom: 1rem;">
                    <ul style="margin: 0; padding-left: 1.5rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ isset($disposition) ? route('inbox.settings.dispositions.update', $disposition->id) : route('inbox.settings.dispositions.store') }}">
                @csrf
                @if(isset($disposition))
                    @method('PUT')
                @endif

                <div class="form-container">
                    <div class="form-group">
                        <label class="form-label" for="name">Name <span style="color: hsl(var(--destructive));">*</span></label>
                        <input type="text" id="name" name="name" class="form-input" 
                               value="{{ old('name', $disposition->name ?? '') }}" 
                               placeholder="e.g., Sales Inquiry, Support Request"
                               required>
                        @error('name')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="color">Color <span style="color: hsl(var(--destructive));">*</span></label>
                        <div class="color-picker-group">
                            <input type="color" id="colorPicker" class="color-input" 
                                   value="{{ old('color', $disposition->color ?? '#6B7280') }}"
                                   onchange="updateColorInput(this.value)">
                            <input type="text" id="color" name="color" class="form-input" 
                                   value="{{ old('color', $disposition->color ?? '#6B7280') }}"
                                   placeholder="#6B7280"
                                   pattern="^#[a-fA-F0-9]{6}$"
                                   onchange="updateColorPicker(this.value)"
                                   required
                                   style="width: 120px;">
                            <div class="color-preview" id="colorPreview" 
                                 style="background-color: {{ old('color', $disposition->color ?? '#6B7280') }};"></div>
                        </div>
                        <div class="preset-colors">
                            @php
                                $presetColors = [
                                    '#ef4444', // Red
                                    '#f97316', // Orange
                                    '#f59e0b', // Amber
                                    '#84cc16', // Lime
                                    '#22c55e', // Green
                                    '#10b981', // Emerald
                                    '#14b8a6', // Teal
                                    '#06b6d4', // Cyan
                                    '#0ea5e9', // Sky
                                    '#3b82f6', // Blue
                                    '#6366f1', // Indigo
                                    '#8b5cf6', // Violet
                                    '#a855f7', // Purple
                                    '#d946ef', // Fuchsia
                                    '#ec4899', // Pink
                                    '#f43f5e', // Rose
                                ];
                            @endphp
                            @foreach($presetColors as $presetColor)
                                <div class="preset-color {{ old('color', $disposition->color ?? '#6B7280') == $presetColor ? 'selected' : '' }}" 
                                     style="background-color: {{ $presetColor }};"
                                     onclick="selectColor('{{ $presetColor }}')"></div>
                            @endforeach
                        </div>
                        @error('color')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="description">Description</label>
                        <textarea id="description" name="description" class="form-input form-textarea" 
                                  placeholder="Brief description of this disposition">{{ old('description', $disposition->description ?? '') }}</textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="sort_order">Sort Order</label>
                        <input type="number" id="sort_order" name="sort_order" class="form-input" 
                               value="{{ old('sort_order', $disposition->sort_order ?? 0) }}"
                               placeholder="0"
                               style="width: 120px;">
                        <div class="form-help">Lower numbers appear first in the list</div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> {{ isset($disposition) ? 'Update' : 'Create' }} Disposition
                        </button>
                        <a href="{{ route('inbox.settings.dispositions') }}" class="btn btn-outline">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
function updateColorInput(color) {
    document.getElementById('color').value = color.toUpperCase();
    document.getElementById('colorPreview').style.backgroundColor = color;
    updatePresetSelection(color);
}

function updateColorPicker(color) {
    if (/^#[a-fA-F0-9]{6}$/.test(color)) {
        document.getElementById('colorPicker').value = color;
        document.getElementById('colorPreview').style.backgroundColor = color;
        updatePresetSelection(color);
    }
}

function selectColor(color) {
    document.getElementById('color').value = color.toUpperCase();
    document.getElementById('colorPicker').value = color;
    document.getElementById('colorPreview').style.backgroundColor = color;
    updatePresetSelection(color);
}

function updatePresetSelection(color) {
    document.querySelectorAll('.preset-color').forEach(el => {
        if (el.style.backgroundColor === color || 
            rgbToHex(el.style.backgroundColor).toUpperCase() === color.toUpperCase()) {
            el.classList.add('selected');
        } else {
            el.classList.remove('selected');
        }
    });
}

function rgbToHex(rgb) {
    if (rgb.startsWith('#')) return rgb;
    const result = rgb.match(/\d+/g);
    if (!result) return '#000000';
    return "#" + ((1 << 24) + (+result[0] << 16) + (+result[1] << 8) + +result[2]).toString(16).slice(1);
}

// Initialize color preview on load
document.addEventListener('DOMContentLoaded', function() {
    const colorValue = document.getElementById('color').value;
    updateColorPicker(colorValue);
});
</script>
@endsection
