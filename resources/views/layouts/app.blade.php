<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | HRMS Enterprise</title>
    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }
        summary::-webkit-details-marker {
            display: none;
        }
        summary {
            list-style: none;
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex">

    <!-- Sidebar -->
    <aside class="w-64 bg-slate-900 border-r border-slate-800 flex flex-col z-20 shrink-0">
        <!-- Sidebar Brand -->
        <div class="h-16 px-6 border-b border-slate-800 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/25">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <div>
                <span class="font-bold tracking-wide bg-gradient-to-r from-white to-slate-400 bg-clip-text text-transparent">HRMS Enterprise</span>
                <span class="block text-[10px] text-slate-500 uppercase tracking-widest font-semibold mt-0.5">Console</span>
            </div>
        </div>

        <!-- Sidebar Navigation -->
        <nav class="flex-grow p-4 space-y-1 overflow-y-auto">
            <!-- COMMON LINK -->
            <a href="/dashboard" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition duration-200 {{ request()->is('dashboard*') ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/10' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"></path>
                </svg>
                <span class="text-sm font-medium">Dashboard</span>
            </a>

            <!-- ADMIN NAVIGATION -->
            @if(auth()->user()->hasRole('Admin'))
                <div class="pt-4 pb-2">
                    <span class="px-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Management</span>
                </div>
                
                <a href="/employees" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition duration-200 {{ request()->is('employees*') ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/10' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span class="text-sm font-medium">Employees</span>
                </a>

                <a href="/attendance" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition duration-200 {{ request()->is('attendance*') ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/10' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-medium">Attendance</span>
                </a>

                <a href="/leave" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition duration-200 {{ request()->is('leave') || request()->is('leave/*') ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/10' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="text-sm font-medium">Leaves</span>
                </a>

                <a href="/leave-types" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition duration-200 {{ request()->is('leave-types*') ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/10' : '' }}">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <span class="text-sm font-medium">Leave Types</span>
                </a>

                <a href="/leave-policies" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition duration-200 {{ request()->is('leave-policies*') ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/10' : '' }}">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                    </svg>
                    <span class="text-sm font-medium">Leave Policies</span>
                </a>

                <a href="/holidays" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition duration-200 {{ request()->is('holidays*') ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/10' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="text-sm font-medium">Holidays</span>
                </a>

                <a href="/announcements" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition duration-200 {{ request()->is('announcements*') ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/10' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <span class="text-sm font-medium">Announcements</span>
                </a>

                <a href="/payroll" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition duration-200 {{ request()->is('payroll*') ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/10' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-medium">Payroll</span>
                </a>

                <div class="pt-4 pb-2">
                    <span class="px-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Organization Setup</span>
                </div>

                <a href="/departments" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition duration-200 {{ request()->is('departments*') ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/10' : '' }}">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <span class="text-sm font-medium">Departments</span>
                </a>

                <a href="/designations" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition duration-200 {{ request()->is('designations*') ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/10' : '' }}">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    <span class="text-sm font-medium">Designations</span>
                </a>

                <a href="/locations" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition duration-200 {{ request()->is('locations*') ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/10' : '' }}">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="text-sm font-medium">Locations</span>
                </a>

                <a href="/shifts" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition duration-200 {{ request()->is('shifts*') ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/10' : '' }}">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-medium">Shifts</span>
                </a>

                <a href="/office-timings" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition duration-200 {{ request()->is('office-timings*') ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/10' : '' }}">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="text-sm font-medium">Office Timings</span>
                </a>

                <a href="/org-structure" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition duration-200 {{ request()->is('org-structure*') ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/10' : '' }}">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <span class="text-sm font-medium">Org Structure</span>
                </a>

                <div class="pt-4 pb-2">
                    <span class="px-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider">System</span>
                </div>

                <a href="/reports" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition duration-200 {{ request()->is('reports*') ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/10' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H3a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="text-sm font-medium">Reports</span>
                </a>

                <a href="/roles" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition duration-200 {{ request()->is('roles*') ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/10' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    <span class="text-sm font-medium">Roles Management</span>
                </a>

                <a href="/permissions" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition duration-200 {{ request()->is('permissions*') ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/10' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"></path>
                    </svg>
                    <span class="text-sm font-medium">Access Matrix</span>
                </a>

                <a href="/settings" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition duration-200 {{ request()->is('settings*') ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/10' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="text-sm font-medium">Settings</span>
                </a>
            @endif

            <!-- MANAGER NAVIGATION -->
            @if(auth()->user()->hasRole('Manager'))
                <div class="pt-4 pb-2">
                    <span class="px-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Team</span>
                </div>

                <a href="/team-members" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition duration-200 {{ request()->is('team-members*') ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/10' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span class="text-sm font-medium">Team Members</span>
                </a>

                <a href="/attendance" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition duration-200 {{ request()->is('attendance*') && !request()->is('attendance/corrections*') ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/10' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-medium">Team Attendance</span>
                </a>

                <a href="/attendance/corrections" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition duration-200 {{ request()->is('attendance/corrections*') ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/10' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    <span class="text-sm font-medium">Correction Requests</span>
                </a>

                <a href="/leave" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition duration-200 {{ request()->is('leave*') ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/10' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="text-sm font-medium">Leave Requests</span>
                </a>

                <a href="/team-calendar" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition duration-200 {{ request()->is('team-calendar*') ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/10' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="text-sm font-medium">Team Calendar</span>
                </a>

                <a href="/team-reports" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition duration-200 {{ request()->is('team-reports*') ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/10' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H3a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="text-sm font-medium">Team Reports</span>
                </a>

                <div class="pt-4 pb-2">
                    <span class="px-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Company</span>
                </div>

                <a href="/holidays" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition duration-200 {{ request()->is('holidays*') ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/10' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="text-sm font-medium">Holidays</span>
                </a>

                <a href="/announcements" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition duration-200 {{ request()->is('announcements*') ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/10' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <span class="text-sm font-medium">Announcements</span>
                </a>
            @endif

            <!-- EMPLOYEE NAVIGATION -->
            @if(auth()->user()->hasRole('Employee'))
                <div class="pt-4 pb-2">
                    <span class="px-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Self Service</span>
                </div>

                <!-- My Attendance Collapsible Menu -->
                <details class="group space-y-1 mb-1" {{ request()->is('timecard*') || request()->is('attendance/my-history*') || request()->is('attendance/corrections*') ? 'open' : '' }}>
                    <summary class="list-none w-full flex items-center justify-between px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition duration-200 cursor-pointer {{ request()->is('timecard*') || request()->is('attendance/my-history*') || request()->is('attendance/corrections*') ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/10' : '' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-sm font-medium">My Attendance</span>
                        </div>
                        <svg class="w-4 h-4 transition-transform duration-200 group-open:rotate-180 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </summary>
                    <div class="pl-12 space-y-1 pt-1 pb-1">
                        <a href="/timecard" class="block py-1.5 text-xs font-medium text-slate-400 hover:text-white transition duration-150 {{ request()->is('timecard*') ? 'text-indigo-400 font-semibold' : '' }}">
                            Daily Attendance
                        </a>
                        <a href="/attendance/my-history" class="block py-1.5 text-xs font-medium text-slate-400 hover:text-white transition duration-150 {{ request()->is('attendance/my-history') && !request()->has('view') ? 'text-indigo-400 font-semibold' : '' }}">
                            Monthly Summary
                        </a>
                        <a href="/attendance/my-history?view=list" class="block py-1.5 text-xs font-medium text-slate-400 hover:text-white transition duration-150 {{ request()->is('attendance/my-history') && request()->get('view') == 'list' ? 'text-indigo-400 font-semibold' : '' }}">
                            Attendance History
                        </a>
                        <a href="/attendance/corrections" class="block py-1.5 text-xs font-medium text-slate-400 hover:text-white transition duration-150 {{ request()->is('attendance/corrections*') ? 'text-indigo-400 font-semibold' : '' }}">
                            Correction Requests
                        </a>
                    </div>
                </details>

                <!-- My Leaves Collapsible Menu -->
                <details class="group space-y-1 mb-1" {{ request()->is('leave*') ? 'open' : '' }}>
                    <summary class="list-none w-full flex items-center justify-between px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition duration-200 cursor-pointer {{ request()->is('leave*') ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/10' : '' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span class="text-sm font-medium">My Leaves</span>
                        </div>
                        <svg class="w-4 h-4 transition-transform duration-200 group-open:rotate-180 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </summary>
                    <div class="pl-12 space-y-1 pt-1 pb-1">
                        <a href="/leave/create" class="block py-1.5 text-xs font-medium text-slate-400 hover:text-white transition duration-150 {{ request()->is('leave/create') ? 'text-indigo-400 font-semibold' : '' }}">
                            Apply Leave
                        </a>
                        <a href="/leave?tab=balance" class="block py-1.5 text-xs font-medium text-slate-400 hover:text-white transition duration-150 {{ request()->is('leave') && request()->get('tab') == 'balance' ? 'text-indigo-400 font-semibold' : '' }}">
                            Leave Balance
                        </a>
                        <a href="/leave?tab=history" class="block py-1.5 text-xs font-medium text-slate-400 hover:text-white transition duration-150 {{ request()->is('leave') && (request()->get('tab') == 'history' || !request()->has('tab')) ? 'text-indigo-400 font-semibold' : '' }}">
                            Leave History
                        </a>
                        <a href="/leave?tab=status" class="block py-1.5 text-xs font-medium text-slate-400 hover:text-white transition duration-150 {{ request()->is('leave') && request()->get('tab') == 'status' ? 'text-indigo-400 font-semibold' : '' }}">
                            Leave Status
                        </a>
                    </div>
                </details>

                <a href="/holiday-calendar" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition duration-200 {{ request()->is('holiday-calendar*') ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/10' : '' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="text-sm font-medium">Holidays</span>
                </a>

                <a href="/announcements" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition duration-200 {{ request()->is('announcements*') ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/10' : '' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <span class="text-sm font-medium">Announcements</span>
                </a>

                <a href="/my-payslips" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition duration-200 {{ request()->is('my-payslips*') || request()->is('payslips*') ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/10' : '' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-medium">Payslips</span>
                </a>
            @endif

            <div class="pt-4 pb-2">
                <span class="px-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Account</span>
            </div>

            <a href="/profile" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition duration-200 {{ request()->is('profile*') ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/10' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span class="text-sm font-medium">My Profile</span>
            </a>

            <a href="/change-password" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition duration-200 {{ request()->is('change-password*') ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/10' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                <span class="text-sm font-medium">Change Password</span>
            </a>

            <a href="/sessions" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/50 transition duration-200 {{ request()->is('sessions*') ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/10' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                <span class="text-sm font-medium">Active Sessions</span>
            </a>
        </nav>

        <!-- Sidebar User Footer -->
        <div class="p-4 border-t border-slate-800 bg-slate-900/50 flex items-center justify-between">
            <div class="flex items-center gap-3 overflow-hidden">
                <div class="w-10 h-10 rounded-xl bg-slate-800 flex items-center justify-center font-bold text-indigo-400 shrink-0 border border-slate-700">
                    {{ substr(auth()->user()->name, 0, 2) }}
                </div>
                <div class="overflow-hidden">
                    <span class="block text-sm font-semibold truncate">{{ auth()->user()->name }}</span>
                    <span class="block text-xs text-slate-400 truncate">{{ auth()->user()->employeeDetail?->designation?->title ?? auth()->user()->roles->first()?->name }}</span>
                </div>
            </div>
            <form action="/logout" method="POST" class="shrink-0">
                @csrf
                <button type="submit" class="p-2 hover:bg-slate-800 rounded-xl text-slate-400 hover:text-rose-400 transition duration-200 cursor-pointer" title="Log Out">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="flex-grow flex flex-col min-w-0 min-h-screen overflow-x-hidden">
        
        <!-- Header / Top Bar -->
        <header class="h-16 border-b border-slate-800 bg-slate-900/50 backdrop-blur-md px-6 flex items-center justify-between sticky top-0 z-10">
            <!-- Breadcrumbs / Page Title -->
            <div class="flex items-center gap-3">
                <h1 class="text-lg font-bold text-slate-100">@yield('page_title', 'Dashboard')</h1>
            </div>

            <!-- Top Bar Actions -->
            <div class="flex items-center gap-4">
                <!-- Notifications Dropdown -->
                <div class="relative" id="notification-bell-container">
                    <button id="notification-bell-btn" class="p-2 hover:bg-slate-800 rounded-xl text-slate-400 hover:text-white transition duration-200 relative cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <span id="notification-badge" class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-rose-500 rounded-full hidden border border-slate-900"></span>
                    </button>

                    <!-- Dropdown Content Panel -->
                    <div id="notification-dropdown-panel" class="absolute right-0 mt-2 w-80 bg-slate-900/95 backdrop-blur-md border border-slate-800 rounded-2xl shadow-2xl z-30 hidden overflow-hidden">
                        <div class="p-4 border-b border-slate-800 flex items-center justify-between">
                            <span class="font-bold text-sm text-slate-200">Notifications</span>
                            <button id="notification-read-all-btn" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300 transition cursor-pointer">Mark all read</button>
                        </div>
                        <div id="notification-list-container" class="max-h-72 overflow-y-auto divide-y divide-slate-800/50">
                            <!-- Dynamically loaded notification items -->
                            <div class="p-4 text-center text-xs text-slate-500">Loading...</div>
                        </div>
                        <div class="p-3 bg-slate-900/80 border-t border-slate-800 text-center">
                            <a href="/notifications" class="text-xs font-bold text-slate-400 hover:text-white transition duration-200 block">View All Notifications</a>
                        </div>
                    </div>
                </div>

                <!-- Info Badges -->
                <div class="px-3 py-1 bg-slate-800 border border-slate-700 text-xs font-semibold text-indigo-400 rounded-lg select-none">
                    {{ auth()->user()->roles->first()?->name }}
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-grow p-6 relative overflow-x-hidden">
            <!-- Glowing accent inside main content -->
            <div class="absolute top-[-10%] right-[-10%] w-[300px] h-[300px] rounded-full bg-indigo-500/5 blur-[80px] pointer-events-none"></div>

            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="h-12 border-t border-slate-900 bg-slate-950 px-6 flex items-center justify-between text-xs text-slate-500">
            <span>HRMS Enterprise v1.0</span>
            <span>&copy; 2026 Company Management</span>
        </footer>
    </div>

    <!-- Notifications Bell script -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const bellBtn = document.getElementById('notification-bell-btn');
        const badge = document.getElementById('notification-badge');
        const panel = document.getElementById('notification-dropdown-panel');
        const listContainer = document.getElementById('notification-list-container');
        const readAllBtn = document.getElementById('notification-read-all-btn');
        const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfTokenElement ? csrfTokenElement.getAttribute('content') : '';

        let notifications = [];

        // Fetch notifications list & count
        function loadNotifications() {
            fetch('/notifications', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(res => {
                if (res.success) {
                    notifications = res.notifications.data;
                    const unreadCount = res.unread_count;

                    // Update badge
                    if (unreadCount > 0) {
                        badge.classList.remove('hidden');
                        readAllBtn.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                        readAllBtn.classList.add('hidden');
                    }

                    // Render list
                    renderNotifications(notifications);
                }
            })
            .catch(err => console.error('Error fetching notifications:', err));
        }

        // Initial load on page load
        loadNotifications();

        // Render helper
        function renderNotifications(items) {
            if (!items || items.length === 0) {
                listContainer.innerHTML = '<div class="p-6 text-center text-xs text-slate-500">No new notifications.</div>';
                return;
            }

            let html = '';
            items.slice(0, 5).forEach(item => {
                const notif = item.notification;
                if (!notif) return;

                // Class colors based on priority
                let priorityDot = 'bg-slate-500';
                if (notif.priority === 'critical') priorityDot = 'bg-rose-500';
                else if (notif.priority === 'high') priorityDot = 'bg-amber-500';
                else if (notif.priority === 'medium') priorityDot = 'bg-indigo-500';

                const unreadClass = item.status !== 'read' ? 'bg-slate-800/30 font-semibold' : 'text-slate-400';
                const dateStr = new Date(item.created_at || notif.created_at).toLocaleDateString(undefined, {month: 'short', day: 'numeric', hour: '2-digit', minute:'2-digit'});

                html += `
                    <div class="p-3 hover:bg-slate-800/40 transition duration-150 flex items-start gap-3 cursor-pointer ${unreadClass}" data-id="${notif.id}">
                        <span class="w-2 h-2 mt-1.5 rounded-full shrink-0 ${priorityDot}"></span>
                        <div class="flex-grow min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-xs text-slate-300 truncate font-semibold">${notif.title}</span>
                                <span class="text-[10px] text-slate-500 whitespace-nowrap">${dateStr}</span>
                            </div>
                            <p class="text-xs text-slate-400 mt-0.5 line-clamp-2">${notif.subject}</p>
                        </div>
                    </div>
                `;
            });
            listContainer.innerHTML = html;

            // Register click listeners on notification items
            listContainer.querySelectorAll('[data-id]').forEach(el => {
                el.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    markAsRead(id);
                });
            });
        }

        // Mark single as read
        function markAsRead(id) {
            fetch(`/notifications/${id}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(res => {
                if (res.success) {
                    loadNotifications();
                    // Redirect to notifications index
                    window.location.href = '/notifications';
                }
            })
            .catch(err => console.error('Error marking as read:', err));
        }

        // Mark all as read
        readAllBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            fetch('/notifications/read-all', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(res => {
                if (res.success) {
                    loadNotifications();
                }
            })
            .catch(err => console.error('Error marking all as read:', err));
        });

        // Toggle Dropdown
        bellBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            panel.classList.toggle('hidden');
        });

        // Close on click outside
        document.addEventListener('click', function (e) {
            if (!panel.contains(e.target) && !bellBtn.contains(e.target)) {
                panel.classList.add('hidden');
            }
        });
    });
    </script>

    <!-- Custom Delete Confirmation Modal -->
    <div id="delete-confirm-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity duration-300 opacity-0" id="delete-modal-backdrop"></div>
        
        <!-- Modal Content -->
        <div class="relative w-full max-w-md bg-slate-900 border border-white/10 rounded-2xl p-6 shadow-2xl transform transition-all duration-300 scale-95 opacity-0 z-10" id="delete-modal-content">
            <div class="flex flex-col items-center text-center">
                <!-- Warning Icon -->
                <div class="w-12 h-12 rounded-xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-400 mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                
                <h3 class="text-base font-bold text-white mb-2">Confirm Action</h3>
                <p class="text-xs text-slate-400 leading-relaxed mb-6" id="delete-modal-message">Are you sure you want to proceed? This action cannot be undone.</p>
                
                <!-- Actions -->
                <div class="flex gap-3 w-full">
                    <button type="button" id="btn-cancel-delete" class="flex-1 px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold rounded-xl text-xs border border-white/5 transition duration-200 cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" id="btn-confirm-delete" class="flex-1 px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-xl text-xs shadow-lg shadow-rose-600/20 transition duration-200 cursor-pointer">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('delete-confirm-modal');
        const backdrop = document.getElementById('delete-modal-backdrop');
        const modalContent = document.getElementById('delete-modal-content');
        const cancelBtn = document.getElementById('btn-cancel-delete');
        const confirmBtn = document.getElementById('btn-confirm-delete');
        const messageEl = document.getElementById('delete-modal-message');
        let formToSubmit = null;

        window.confirmDelete = function(event, form, message) {
            event.preventDefault();
            formToSubmit = form;
            
            if (message) {
                messageEl.textContent = message;
            } else {
                messageEl.textContent = 'Are you sure you want to proceed? This action cannot be undone.';
            }
            
            // Show modal and apply animations
            modal.classList.remove('hidden');
            setTimeout(() => {
                backdrop.classList.remove('opacity-0');
                backdrop.classList.add('opacity-100');
                modalContent.classList.remove('scale-95', 'opacity-0');
                modalContent.classList.add('scale-100', 'opacity-100');
            }, 10);
        };

        function closeModal() {
            backdrop.classList.remove('opacity-100');
            backdrop.classList.add('opacity-0');
            modalContent.classList.remove('scale-100', 'opacity-100');
            modalContent.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                formToSubmit = null;
            }, 300);
        }

        cancelBtn.addEventListener('click', closeModal);
        
        confirmBtn.addEventListener('click', function() {
            if (formToSubmit) {
                formToSubmit.submit();
            }
            closeModal();
        });

        // Close on backdrop click
        backdrop.addEventListener('click', closeModal);
    });
    </script>
</body>
</html>
