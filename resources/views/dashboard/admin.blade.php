@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('page_title', 'System Administration Dashboard')

@section('content')
<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    
    <!-- Total Employees -->
    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-6 shadow-xl relative overflow-hidden group hover:border-indigo-500/30 transition duration-300">
        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition duration-300 text-indigo-400">
            <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a7 7 0 00-7 7v1h12v-1a7 7 0 00-7-7z"></path>
            </svg>
        </div>
        <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Total Employees</span>
        <span class="block text-4xl font-bold text-slate-100 mt-2">{{ $totalEmployees }}</span>
        <span class="block text-[10px] text-emerald-400 mt-1 font-medium">Synced in system</span>
    </div>

    <!-- Active Employees -->
    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-6 shadow-xl relative overflow-hidden group hover:border-emerald-500/30 transition duration-300">
        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition duration-300 text-emerald-400">
            <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"></path>
            </svg>
        </div>
        <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Active Status</span>
        <span class="block text-4xl font-bold text-slate-100 mt-2">{{ $activeEmployees }}</span>
        <span class="block text-[10px] text-emerald-400 mt-1 font-medium">100% profile complete</span>
    </div>

    <!-- Present Today -->
    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-6 shadow-xl relative overflow-hidden group hover:border-purple-500/30 transition duration-300">
        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition duration-300 text-purple-400">
            <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
            </svg>
        </div>
        <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Present Today</span>
        <span class="block text-4xl font-bold text-slate-100 mt-2">{{ $presentToday }}</span>
        <span class="block text-[10px] text-purple-400 mt-1 font-medium">Clock-in active</span>
    </div>

    <!-- Pending Leave Approvals -->
    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-6 shadow-xl relative overflow-hidden group hover:border-rose-500/30 transition duration-300">
        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition duration-300 text-rose-400">
            <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
            </svg>
        </div>
        <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Pending Leaves</span>
        <span class="block text-4xl font-bold text-slate-100 mt-2">{{ $pendingLeaves }}</span>
        <span class="block text-[10px] text-rose-400 mt-1 font-medium">Requires attention</span>
    </div>

</div>

<!-- Second Row: Graph Placeholder & Info Cards -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Payroll Payout Widget (takes 2 cols on wide screens) -->
    <div class="lg:col-span-2 backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-6 shadow-xl relative">
        <h2 class="text-lg font-bold mb-4 text-slate-100 flex items-center justify-between">
            <span>Payroll Run Summary</span>
            <span class="text-xs px-3 py-1 rounded-full bg-indigo-500/10 text-indigo-400 font-semibold border border-indigo-500/10">Active Month</span>
        </h2>
        
        <!-- Premium mini metrics inside panel -->
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-slate-900/60 p-4 rounded-xl border border-white/5">
                <span class="text-[10px] uppercase font-bold text-slate-500">Last Payout</span>
                <span class="block text-xl font-bold text-slate-200 mt-1">{{ $payrollSummary }}</span>
            </div>
            <div class="bg-slate-900/60 p-4 rounded-xl border border-white/5">
                <span class="text-[10px] uppercase font-bold text-slate-500">Target Month</span>
                <span class="block text-xl font-bold text-slate-200 mt-1">June 2026</span>
            </div>
            <div class="bg-slate-900/60 p-4 rounded-xl border border-white/5">
                <span class="text-[10px] uppercase font-bold text-slate-500">Status</span>
                <span class="block text-xl font-bold text-amber-400 mt-1">Ready</span>
            </div>
        </div>

        <div class="h-48 bg-slate-950/50 rounded-xl border border-white/5 flex items-center justify-center relative overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-tr from-indigo-500/5 to-purple-500/5"></div>
            <div class="z-10 text-center">
                <svg class="w-12 h-12 text-slate-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <p class="text-sm font-semibold text-slate-300">Analytical charts will populate during Payroll runs</p>
                <p class="text-xs text-slate-500 mt-1">Phase 6 Payroll integration module</p>
            </div>
        </div>
    </div>

    <!-- Right Side: Holiday List & Recent Activity -->
    <div class="space-y-6">
        
        <!-- Upcoming Holidays -->
        <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-6 shadow-xl">
            <h3 class="text-base font-bold mb-4 text-slate-100">Upcoming Holidays</h3>
            <div class="space-y-3">
                @foreach($upcomingHolidays as $holiday)
                    <div class="flex items-center justify-between p-3 bg-slate-900/60 rounded-xl border border-white/5">
                        <div>
                            <span class="block text-sm font-semibold text-slate-200">{{ $holiday['name'] }}</span>
                            <span class="block text-[11px] text-slate-400 mt-0.5">{{ $holiday['date'] }}</span>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-1 bg-indigo-500/10 text-indigo-400 rounded-lg border border-indigo-500/10">
                            {{ $holiday['days_left'] }} days left
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-6 shadow-xl">
            <h3 class="text-base font-bold mb-4 text-slate-100">Recent Activities</h3>
            <div class="space-y-4">
                @foreach($activities as $activity)
                    <div class="flex gap-3">
                        <div class="w-2 h-2 rounded-full bg-indigo-500 mt-1.5 shrink-0"></div>
                        <div class="flex-grow">
                            <span class="block text-xs text-slate-200">{{ $activity['text'] }}</span>
                            <span class="block text-[10px] text-slate-500 mt-0.5">{{ $activity['time'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

</div>
@endsection
