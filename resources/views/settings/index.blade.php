@extends('layouts.app')

@section('title', 'System Settings')
@section('page_title', 'Platform Administration')

@section('content')
<div class="space-y-6 max-w-6xl mx-auto relative">
    <!-- Toast Notification Container -->
    <div id="toast-container" class="fixed top-6 right-6 z-50 flex flex-col gap-2 pointer-events-none"></div>

    <!-- Top Banner -->
    <div class="bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md relative overflow-hidden">
        <div class="absolute top-[-20%] right-[-10%] w-[250px] h-[250px] rounded-full bg-indigo-500/10 blur-[60px] pointer-events-none"></div>
        <h2 class="text-xl font-bold text-white">System Configurations</h2>
        <p class="text-slate-400 text-sm mt-1">Control application parameters, mail settings, attendance thresholds, and feature flags.</p>
    </div>

    <!-- Navigation Tabs & Form Panels -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Sidebar Navigation Tabs -->
        <div class="lg:col-span-1 bg-white/5 border border-white/10 rounded-2xl p-4 backdrop-blur-md h-fit space-y-1">
            <button onclick="switchTab(event, 'tab-company')" class="tab-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm text-left transition duration-150 bg-indigo-600/10 text-indigo-400 border border-indigo-500/10 cursor-pointer">
                Company Profile
            </button>
            <button onclick="switchTab(event, 'tab-system')" class="tab-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm text-left transition duration-150 text-slate-400 hover:text-slate-200 hover:bg-white/5 cursor-pointer">
                System Options
            </button>
            <button onclick="switchTab(event, 'tab-email')" class="tab-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm text-left transition duration-150 text-slate-400 hover:text-slate-200 hover:bg-white/5 cursor-pointer">
                SMTP Mail Settings
            </button>
            <button onclick="switchTab(event, 'tab-notification')" class="tab-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm text-left transition duration-150 text-slate-400 hover:text-slate-200 hover:bg-white/5 cursor-pointer">
                Notification Engine
            </button>
            <button onclick="switchTab(event, 'tab-attendance')" class="tab-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm text-left transition duration-150 text-slate-400 hover:text-slate-200 hover:bg-white/5 cursor-pointer">
                Attendance Policy
            </button>
            <button onclick="switchTab(event, 'tab-leave')" class="tab-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm text-left transition duration-150 text-slate-400 hover:text-slate-200 hover:bg-white/5 cursor-pointer">
                Leave Allocations
            </button>
            <button onclick="switchTab(event, 'tab-payroll')" class="tab-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm text-left transition duration-150 text-slate-400 hover:text-slate-200 hover:bg-white/5 cursor-pointer">
                Payroll Controls
            </button>
            <button onclick="switchTab(event, 'tab-security')" class="tab-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm text-left transition duration-150 text-slate-400 hover:text-slate-200 hover:bg-white/5 cursor-pointer">
                Security & Session
            </button>
            <button onclick="switchTab(event, 'tab-storage')" class="tab-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm text-left transition duration-150 text-slate-400 hover:text-slate-200 hover:bg-white/5 cursor-pointer">
                File Storage Disk
            </button>
            <button onclick="switchTab(event, 'tab-backup')" class="tab-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm text-left transition duration-150 text-slate-400 hover:text-slate-200 hover:bg-white/5 cursor-pointer">
                Backup Scheduler
            </button>
            <button onclick="switchTab(event, 'tab-flags')" class="tab-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm text-left transition duration-150 text-slate-400 hover:text-slate-200 hover:bg-white/5 cursor-pointer">
                Feature Flags
            </button>
        </div>

        <!-- Form Panels -->
        <div class="lg:col-span-3 space-y-6">
            <!-- GROUP: Company Profile -->
            <div id="tab-company" class="tab-content bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md">
                <form onsubmit="saveSettings(event, 'company')" class="space-y-6">
                    <h3 class="text-lg font-bold text-indigo-400 border-b border-white/5 pb-2">Company Registry Profile</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Company Name</label>
                            <input type="text" name="company_name" value="{{ $company->company_name ?? '' }}" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Company Code</label>
                            <input type="text" name="company_code" value="{{ $company->company_code ?? '' }}" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Primary Email</label>
                            <input type="email" name="email" value="{{ $company->email ?? '' }}" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Phone</label>
                            <input type="tel" name="phone" value="{{ $company->phone ?? '' }}" oninput="this.value = this.value.replace(/[^0-9+\-\s()]/g, '');" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Website URL</label>
                            <input type="url" name="website" value="{{ $company->website ?? '' }}" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Tax Registration (VAT/GST/EIN)</label>
                            <input type="text" name="tax_number" value="{{ $company->tax_number ?? '' }}" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>
                        <div class="md:col-span-2 flex items-center gap-6 p-4 bg-slate-900/50 rounded-xl border border-slate-800">
                            <div class="w-20 h-20 rounded-xl bg-slate-800 flex items-center justify-center overflow-hidden border border-slate-700 shrink-0">
                                @if(!empty($company->company_logo))
                                    <img src="{{ asset($company->company_logo) }}" id="logo-preview" class="w-full h-full object-contain">
                                @else
                                    <svg id="logo-placeholder" class="w-10 h-10 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                @endif
                            </div>
                            <div class="flex-grow">
                                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Company Logo</label>
                                <input type="file" name="company_logo" id="company_logo_input" accept="image/*" onchange="previewLogo(this)" class="w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-600/10 file:text-indigo-400 hover:file:bg-indigo-600/20 cursor-pointer">
                                <p class="text-[11px] text-slate-500 mt-2">PNG, JPG, or SVG. Suggested size: 250x60px.</p>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Street Address</label>
                            <input type="text" name="address" value="{{ $company->address ?? '' }}" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-white/5">
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm transition shadow shadow-indigo-500/25 cursor-pointer">Save Company Info</button>
                    </div>
                </form>
            </div>

            <!-- GROUP: System Options -->
            <div id="tab-system" class="tab-content bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md hidden">
                <form onsubmit="saveSettings(event, 'system')" class="space-y-6">
                    <h3 class="text-lg font-bold text-indigo-400 border-b border-white/5 pb-2">Global System Parameters</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">App Name</label>
                            <input type="text" name="app_name" value="{{ $system->app_name ?? '' }}" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Version</label>
                            <input type="text" name="app_version" value="{{ $system->app_version ?? '' }}" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Default Timezone</label>
                            <input type="text" name="default_timezone" value="{{ $system->default_timezone ?? 'UTC' }}" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Currency Symbol</label>
                            <input type="text" name="default_currency" value="{{ $system->default_currency ?? 'USD' }}" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">System Mode</label>
                            <select name="system_status" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                                <option value="online" {{ ($system->system_status ?? 'online') === 'online' ? 'selected' : '' }}>Online</option>
                                <option value="maintenance" {{ ($system->system_status ?? 'online') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Date Format</label>
                            <input type="text" name="date_format" value="{{ $system->date_format ?? 'Y-m-d' }}" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Time Format</label>
                            <input type="text" name="time_format" value="{{ $system->time_format ?? 'H:i' }}" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Default Locale Language</label>
                            <input type="text" name="language" value="{{ $system->language ?? 'en' }}" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>

                        <div class="md:col-span-2 mt-4">
                            <h4 class="text-sm font-bold text-indigo-400 border-b border-white/5 pb-2 mb-4">Aesthetics & Color Theme</h4>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Theme Mode</label>
                            <select name="theme_mode" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                                <option value="light" {{ ($system->theme_mode ?? 'dark') === 'light' ? 'selected' : '' }}>Light Mode</option>
                                <option value="dark" {{ ($system->theme_mode ?? 'dark') === 'dark' ? 'selected' : '' }}>Dark Mode</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Primary Color (HEX)</label>
                            <div class="flex gap-2">
                                <input type="color" id="primary_color_picker" value="{{ $system->primary_color ?? '#0c75a4' }}" oninput="document.getElementById('primary_color_input').value = this.value" class="w-10 h-10 border-0 bg-transparent cursor-pointer rounded-xl overflow-hidden shrink-0">
                                <input type="text" name="primary_color" id="primary_color_input" value="{{ $system->primary_color ?? '#0c75a4' }}" oninput="document.getElementById('primary_color_picker').value = this.value" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 uppercase">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Secondary Color (HEX)</label>
                            <div class="flex gap-2">
                                <input type="color" id="secondary_color_picker" value="{{ $system->secondary_color ?? '#50535a' }}" oninput="document.getElementById('secondary_color_input').value = this.value" class="w-10 h-10 border-0 bg-transparent cursor-pointer rounded-xl overflow-hidden shrink-0">
                                <input type="text" name="secondary_color" id="secondary_color_input" value="{{ $system->secondary_color ?? '#50535a' }}" oninput="document.getElementById('secondary_color_picker').value = this.value" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 uppercase">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Accent Color (HEX)</label>
                            <div class="flex gap-2">
                                <input type="color" id="accent_color_picker" value="{{ $system->accent_color ?? '#0284c7' }}" oninput="document.getElementById('accent_color_input').value = this.value" class="w-10 h-10 border-0 bg-transparent cursor-pointer rounded-xl overflow-hidden shrink-0">
                                <input type="text" name="accent_color" id="accent_color_input" value="{{ $system->accent_color ?? '#0284c7' }}" oninput="document.getElementById('accent_color_picker').value = this.value" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 uppercase">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Light Mode Background (HEX)</label>
                            <div class="flex gap-2">
                                <input type="color" id="bg_light_picker" value="{{ $system->bg_light ?? '#f8fafc' }}" oninput="document.getElementById('bg_light_input').value = this.value" class="w-10 h-10 border-0 bg-transparent cursor-pointer rounded-xl overflow-hidden shrink-0">
                                <input type="text" name="bg_light" id="bg_light_input" value="{{ $system->bg_light ?? '#f8fafc' }}" oninput="document.getElementById('bg_light_picker').value = this.value" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 uppercase">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Dark Mode Background (HEX)</label>
                            <div class="flex gap-2">
                                <input type="color" id="bg_dark_picker" value="{{ $system->bg_dark ?? '#090d16' }}" oninput="document.getElementById('bg_dark_input').value = this.value" class="w-10 h-10 border-0 bg-transparent cursor-pointer rounded-xl overflow-hidden shrink-0">
                                <input type="text" name="bg_dark" id="bg_dark_input" value="{{ $system->bg_dark ?? '#090d16' }}" oninput="document.getElementById('bg_dark_picker').value = this.value" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 uppercase">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-white/5">
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm transition shadow shadow-indigo-500/25 cursor-pointer">Save System Info</button>
                    </div>
                </form>
            </div>

            <!-- GROUP: SMTP Mail Settings -->
            <div id="tab-email" class="tab-content bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md hidden">
                <form onsubmit="saveSettings(event, 'email')" class="space-y-6">
                    <h3 class="text-lg font-bold text-indigo-400 border-b border-white/5 pb-2">SMTP Mail Configuration</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">SMTP Server Host</label>
                            <input type="text" name="smtp_host" value="{{ $email->smtp_host ?? '' }}" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">SMTP Port</label>
                            <input type="number" name="smtp_port" value="{{ $email->smtp_port ?? 587 }}" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">SMTP Username</label>
                            <input type="text" name="smtp_username" value="{{ $email->smtp_username ?? '' }}" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">SMTP Password</label>
                            <div class="relative">
                                <input type="password" name="smtp_password" id="smtp_password" placeholder="••••••••••••" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 pr-10 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                                <button type="button" onclick="togglePasswordVisibility('smtp_password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white transition cursor-pointer">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Sender Name</label>
                            <input type="text" name="sender_name" value="{{ $email->sender_name ?? '' }}" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Sender Email Address</label>
                            <input type="email" name="sender_email" value="{{ $email->sender_email ?? '' }}" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Encryption Method</label>
                            <select name="encryption" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                                <option value="tls" {{ ($email->encryption ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ ($email->encryption ?? 'tls') === 'ssl' ? 'selected' : '' }}>SSL</option>
                                <option value="none" {{ ($email->encryption ?? 'tls') === 'none' ? 'selected' : '' }}>None</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-white/5">
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm transition shadow shadow-indigo-500/25 cursor-pointer">Save SMTP settings</button>
                    </div>
                </form>
            </div>

            <!-- GROUP: Notification Engine -->
            <div id="tab-notification" class="tab-content bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md hidden">
                <form onsubmit="saveSettings(event, 'notification')" class="space-y-6">
                    <h3 class="text-lg font-bold text-indigo-400 border-b border-white/5 pb-2">System Notification Engine Channels</h3>
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-slate-900/50 rounded-xl border border-slate-800">
                            <div>
                                <span class="block text-sm font-semibold text-white">In-App Notifications</span>
                                <span class="text-xs text-slate-400">Deliver announcements and requests inside the console bell menu.</span>
                            </div>
                            <input type="checkbox" name="in_app_enabled" {{ ($notification->in_app_enabled ?? true) ? 'checked' : '' }} class="w-5 h-5 accent-indigo-500">
                        </div>

                        <div class="flex items-center justify-between p-4 bg-slate-900/50 rounded-xl border border-slate-800">
                            <div>
                                <span class="block text-sm font-semibold text-white">Email Notification Triggers</span>
                                <span class="text-xs text-slate-400">Dispatch SMTP emails for leave approvals, welcome guides, and payslips.</span>
                            </div>
                            <input type="checkbox" name="email_enabled" {{ ($notification->email_enabled ?? true) ? 'checked' : '' }} class="w-5 h-5 accent-indigo-500">
                        </div>

                        <div class="flex items-center justify-between p-4 bg-slate-900/50 rounded-xl border border-slate-800">
                            <div>
                                <span class="block text-sm font-semibold text-white">SMS Alerts</span>
                                <span class="text-xs text-slate-400">Directly alert mobile numbers for critical alerts.</span>
                            </div>
                            <input type="checkbox" name="sms_enabled" {{ ($notification->sms_enabled ?? false) ? 'checked' : '' }} class="w-5 h-5 accent-indigo-500">
                        </div>

                        <div class="flex items-center justify-between p-4 bg-slate-900/50 rounded-xl border border-slate-800">
                            <div>
                                <span class="block text-sm font-semibold text-white">Push Notifications</span>
                                <span class="text-xs text-slate-400">Browser alerts for announcements.</span>
                            </div>
                            <input type="checkbox" name="push_enabled" {{ ($notification->push_enabled ?? false) ? 'checked' : '' }} class="w-5 h-5 accent-indigo-500">
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-white/5">
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm transition shadow shadow-indigo-500/25 cursor-pointer">Save Channels</button>
                    </div>
                </form>
            </div>

            <!-- GROUP: Attendance Policy -->
            <div id="tab-attendance" class="tab-content bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md hidden">
                <form onsubmit="saveSettings(event, 'attendance')" class="space-y-6">
                    <h3 class="text-lg font-bold text-indigo-400 border-b border-white/5 pb-2">Global Attendance Policy Settings</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Grace Period (Minutes)</label>
                            <input type="number" name="grace_period_minutes" value="{{ $attendance->grace_period_minutes ?? 15 }}" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Minimum Full-Day Hours</label>
                            <input type="number" step="0.5" name="minimum_working_hours" value="{{ $attendance->minimum_working_hours ?? 8.0 }}" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Half-Day Working Hours</label>
                            <input type="number" step="0.5" name="half_day_working_hours" value="{{ $attendance->half_day_working_hours ?? 4.0 }}" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Overtime Multiplier Ratio</label>
                            <input type="number" step="0.1" name="overtime_multiplier" value="{{ $attendance->overtime_multiplier ?? 1.5 }}" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-white/5">
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm transition shadow shadow-indigo-500/25 cursor-pointer">Save Attendance Rules</button>
                    </div>
                </form>
            </div>

            <!-- GROUP: Leave Allocations -->
            <div id="tab-leave" class="tab-content bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md hidden">
                <form onsubmit="saveSettings(event, 'leave')" class="space-y-6">
                    <h3 class="text-lg font-bold text-indigo-400 border-b border-white/5 pb-2">Leave Accrual Configurations</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Accrual Refresh Cycle</label>
                            <select name="accrual_cycle" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                                <option value="monthly" {{ ($leave->accrual_cycle ?? 'monthly') === 'monthly' ? 'selected' : '' }}>Monthly Proration</option>
                                <option value="yearly" {{ ($leave->accrual_cycle ?? 'monthly') === 'yearly' ? 'selected' : '' }}>Yearly Upfront</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Maximum Accumulated Days</label>
                            <input type="number" name="max_accumulated_days" value="{{ $leave->max_accumulated_days ?? 30 }}" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>
                        <div class="flex items-center justify-between p-4 bg-slate-900/50 rounded-xl border border-slate-800 md:col-span-2">
                            <div>
                                <span class="block text-sm font-semibold text-white">Carry Forward Remaining Balance</span>
                                <span class="text-xs text-slate-400">Transfer unused leaves to the next calendar cycle automatically.</span>
                            </div>
                            <input type="checkbox" name="carry_forward_enabled" {{ ($leave->carry_forward_enabled ?? true) ? 'checked' : '' }} class="w-5 h-5 accent-indigo-500">
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-white/5">
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm transition shadow shadow-indigo-500/25 cursor-pointer">Save Accrual Rules</button>
                    </div>
                </form>
            </div>

            <!-- GROUP: Payroll Controls -->
            <div id="tab-payroll" class="tab-content bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md hidden">
                <form onsubmit="saveSettings(event, 'payroll')" class="space-y-6">
                    <h3 class="text-lg font-bold text-indigo-400 border-b border-white/5 pb-2">Payroll & Compliance Parameters</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Payroll Run Cycle</label>
                            <select name="payroll_cycle" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                                <option value="monthly" {{ ($payroll->payroll_cycle ?? 'monthly') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="weekly" {{ ($payroll->payroll_cycle ?? 'monthly') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                                <option value="bi-weekly" {{ ($payroll->payroll_cycle ?? 'monthly') === 'bi-weekly' ? 'selected' : '' }}>Bi-Weekly</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Cycle Processing Day (1-31)</label>
                            <input type="number" name="processing_day" min="1" max="31" value="{{ $payroll->processing_day ?? 28 }}" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Provident Fund Contribution (%)</label>
                            <input type="number" step="0.01" name="pf_percentage" value="{{ $payroll->pf_percentage ?? 12.00 }}" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Professional Tax Salary Threshold</label>
                            <input type="number" name="professional_tax_threshold" value="{{ $payroll->professional_tax_threshold ?? 15000 }}" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-white/5">
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm transition shadow shadow-indigo-500/25 cursor-pointer">Save Payroll Rules</button>
                    </div>
                </form>
            </div>

            <!-- GROUP: Security Defaults -->
            <div id="tab-security" class="tab-content bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md hidden">
                <form onsubmit="saveSettings(event, 'security')" class="space-y-6">
                    <h3 class="text-lg font-bold text-indigo-400 border-b border-white/5 pb-2">Access Governance & Passwords</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Minimum Password Characters</label>
                            <input type="number" name="min_password_length" min="6" max="32" value="{{ $security->min_password_length ?? 8 }}" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Password Expiration (Days, 0 = never)</label>
                            <input type="number" name="password_expiry_days" value="{{ $security->password_expiry_days ?? 90 }}" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Failed Login Lockout Tries</label>
                            <input type="number" name="failed_login_attempts" value="{{ $security->failed_login_attempts ?? 5 }}" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Account Lock Duration (Minutes)</label>
                            <input type="number" name="account_lock_minutes" value="{{ $security->account_lock_minutes ?? 15 }}" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Inactivity Timeout (Minutes)</label>
                            <input type="number" name="session_timeout_minutes" value="{{ $security->session_timeout_minutes ?? 30 }}" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-white/5">
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm transition shadow shadow-indigo-500/25 cursor-pointer">Save Security Rules</button>
                    </div>
                </form>
            </div>

            <!-- GROUP: File Storage Disk -->
            <div id="tab-storage" class="tab-content bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md hidden">
                <form onsubmit="saveSettings(event, 'storage')" class="space-y-6">
                    <h3 class="text-lg font-bold text-indigo-400 border-b border-white/5 pb-2">File Storage & Document Storage</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Default Storage Disk</label>
                            <select name="default_disk" id="default_disk" onchange="toggleS3Fields(this.value)" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                                <option value="local" {{ ($storage->default_disk ?? 'local') === 'local' ? 'selected' : '' }}>Local Private Disk</option>
                                <option value="s3" {{ ($storage->default_disk ?? 'local') === 's3' ? 'selected' : '' }}>AWS S3 Bucket</option>
                                <option value="gcs" {{ ($storage->default_disk ?? 'local') === 'gcs' ? 'selected' : '' }}>Google Cloud Storage</option>
                            </select>
                        </div>
                    </div>

                    <!-- AWS S3 Specific Fields -->
                    <div id="s3-fields" class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-white/5 {{ ($storage->default_disk ?? 'local') === 's3' ? '' : 'hidden' }}">
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">AWS Access Key</label>
                            <input type="text" name="s3_key" value="{{ $storage->s3_key ?? '' }}" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">AWS Secret Key</label>
                            <div class="relative">
                                <input type="password" name="s3_secret" id="s3_secret" placeholder="••••••••••••" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 pr-10 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                                <button type="button" onclick="togglePasswordVisibility('s3_secret', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white transition cursor-pointer">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">S3 Region</label>
                            <input type="text" name="s3_region" value="{{ $storage->s3_region ?? 'us-east-1' }}" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">S3 Bucket Name</label>
                            <input type="text" name="s3_bucket" value="{{ $storage->s3_bucket ?? '' }}" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-white/5">
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm transition shadow shadow-indigo-500/25 cursor-pointer">Save Storage Options</button>
                    </div>
                </form>
            </div>

            <!-- GROUP: Backup Scheduler -->
            <div id="tab-backup" class="tab-content bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md hidden">
                <form onsubmit="saveSettings(event, 'backup')" class="space-y-6">
                    <h3 class="text-lg font-bold text-indigo-400 border-b border-white/5 pb-2">Database Backups & Snapshot Engine</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Backup Frequency</label>
                            <select name="backup_frequency" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                                <option value="daily" {{ ($backup->backup_frequency ?? 'daily') === 'daily' ? 'selected' : '' }}>Daily</option>
                                <option value="weekly" {{ ($backup->backup_frequency ?? 'daily') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                                <option value="monthly" {{ ($backup->backup_frequency ?? 'daily') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Run Time (HH:MM)</label>
                            <input type="text" name="backup_time" placeholder="02:00" value="{{ $backup->backup_time ?? '02:00' }}" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Retention Count (Days)</label>
                            <input type="number" name="retention_days" min="1" value="{{ $backup->retention_days ?? 30 }}" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>
                        <div class="flex items-center justify-between p-4 bg-slate-900/50 rounded-xl border border-slate-800 md:col-span-2">
                            <div>
                                <span class="block text-sm font-semibold text-white">Backup User Document Attachments</span>
                                <span class="text-xs text-slate-400">Include file uploads and document vault in the snapshot tarball.</span>
                            </div>
                            <input type="checkbox" name="include_files" {{ ($backup->include_files ?? true) ? 'checked' : '' }} class="w-5 h-5 accent-indigo-500">
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-white/5">
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm transition shadow shadow-indigo-500/25 cursor-pointer">Save Schedule</button>
                    </div>
                </form>
            </div>

            <!-- GROUP: Feature Flags -->
            <div id="tab-flags" class="tab-content bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md hidden">
                <h3 class="text-lg font-bold text-indigo-400 border-b border-white/5 pb-2 mb-6">Application Feature Flags</h3>
                
                <div class="space-y-4">
                    @forelse($featureFlags as $flag)
                        <div class="flex items-center justify-between p-4 bg-slate-900/50 rounded-xl border border-slate-800 hover:border-slate-700 transition duration-150">
                            <div>
                                <span class="block text-sm font-semibold text-white">{{ $flag->flag_key }}</span>
                                <span class="text-xs text-slate-400 mt-1 block">{{ $flag->description ?? 'No description provided.' }}</span>
                            </div>
                            <div>
                                <input type="checkbox" onchange="updateFeature('{{ $flag->flag_key }}', this)" {{ $flag->flag_value ? 'checked' : '' }} class="w-5 h-5 accent-indigo-500 cursor-pointer">
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-500 text-sm">
                            No system feature flags registered.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Tab switching
    function switchTab(evt, tabId) {
        const tabContents = document.getElementsByClassName("tab-content");
        for (let i = 0; i < tabContents.length; i++) {
            tabContents[i].classList.add("hidden");
        }

        const tabBtns = document.getElementsByClassName("tab-btn");
        for (let i = 0; i < tabBtns.length; i++) {
            tabBtns[i].classList.remove("bg-indigo-600/10", "text-indigo-400", "border", "border-indigo-500/10");
            tabBtns[i].classList.add("text-slate-400", "hover:text-slate-200", "hover:bg-white/5");
        }

        document.getElementById(tabId).classList.remove("hidden");
        evt.currentTarget.classList.remove("text-slate-400", "hover:text-slate-200", "hover:bg-white/5");
        evt.currentTarget.classList.add("bg-indigo-600/10", "text-indigo-400", "border", "border-indigo-500/10");
    }

    // Toggle S3 bucket settings
    function toggleS3Fields(disk) {
        const s3Panel = document.getElementById('s3-fields');
        if (disk === 's3') {
            s3Panel.classList.remove('hidden');
        } else {
            s3Panel.classList.add('hidden');
        }
    }

    // Show on-screen toast alert
    function showToast(type, message) {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = `p-4 rounded-xl shadow-2xl flex items-center gap-3 border transition duration-300 text-sm font-semibold pointer-events-auto bg-slate-900/90 backdrop-blur-md ${
            type === 'success' ? 'border-emerald-500/30 text-emerald-400' : 'border-rose-500/30 text-rose-400'
        }`;
        
        toast.innerHTML = `
            <span class="w-2 h-2 rounded-full ${type === 'success' ? 'bg-emerald-500' : 'bg-rose-500'} animate-ping"></span>
            <span>${message}</span>
        `;
        
        container.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-y-[-10px]');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Preview logo on selection
    function previewLogo(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                let preview = document.getElementById('logo-preview');
                const placeholder = document.getElementById('logo-placeholder');
                
                if (preview) {
                    preview.src = e.target.result;
                } else if (placeholder) {
                    const img = document.createElement('img');
                    img.id = 'logo-preview';
                    img.className = 'w-full h-full object-contain';
                    img.src = e.target.result;
                    placeholder.parentNode.appendChild(img);
                    placeholder.remove();
                }
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Submit group settings via AJAX using FormData to allow file uploads (spoofed as PUT)
    async function saveSettings(event, group) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        
        // Add Laravel's method spoofing so that it is processed as PUT
        formData.append('_method', 'PUT');

        // Handle checkboxes (explicit false value when unchecked)
        form.querySelectorAll('input[type="checkbox"]').forEach(cb => {
            if (!formData.has(cb.name)) {
                formData.append(cb.name, '0');
            }
        });

        try {
            const response = await fetch(`/settings/${group}`, {
                method: 'POST', // Spoofed to PUT
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            });
            const res = await response.json();
            if (response.ok) {
                showToast('success', res.message || 'Settings updated successfully.');
                
                // If company or system settings changed, refresh after 1 second to apply theme/logo updates
                if (group === 'company' || group === 'system') {
                    setTimeout(() => window.location.reload(), 1000);
                }
            } else {
                showToast('error', res.message || 'Verification failed. Review form input fields.');
            }
        } catch (err) {
            showToast('error', 'Network error. Failed to save configuration.');
        }
    }

    // Submit single Feature Flag update
    async function updateFeature(flagKey, checkbox) {
        const isEnabled = checkbox.checked;
        try {
            const response = await fetch('/feature-flags', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    flag_key: flagKey,
                    flag_value: isEnabled
                })
            });
            const res = await response.json();
            if (response.ok) {
                showToast('success', res.message || 'Feature flag updated.');
            } else {
                showToast('error', res.message || 'Failed to update feature flag.');
                checkbox.checked = !isEnabled;
            }
        } catch (err) {
            showToast('error', 'Network error updating feature flag.');
            checkbox.checked = !isEnabled;
        }
    }

    function togglePasswordVisibility(inputId, button) {
        const input = document.getElementById(inputId);
        const svg = button.querySelector('svg');
        if (input.type === 'password') {
            input.type = 'text';
            svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path>';
        } else {
            input.type = 'password';
            svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
        }
    }
</script>
@endsection
