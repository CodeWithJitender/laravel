@extends('layouts.app')

@section('title', 'Manager Dashboard')
@section('page_title', 'Team Management Dashboard')

@section('content')
<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    
    <!-- Team Size -->
    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-6 shadow-xl relative overflow-hidden group hover:border-indigo-500/30 transition duration-300">
        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition duration-300 text-indigo-400">
            <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a7 7 0 00-7 7v1h12v-1a7 7 0 00-7-7z"></path>
            </svg>
        </div>
        <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Team Size</span>
        <span class="block text-4xl font-bold text-slate-100 mt-2">{{ $teamSize }}</span>
        <span class="block text-[10px] text-indigo-400 mt-1 font-medium">Direct reports</span>
    </div>

    <!-- Team Present -->
    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-6 shadow-xl relative overflow-hidden group hover:border-emerald-500/30 transition duration-300">
        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition duration-300 text-emerald-400">
            <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
            </svg>
        </div>
        <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Team Present Today</span>
        <span class="block text-4xl font-bold text-slate-100 mt-2">{{ $teamPresent }}</span>
        <span class="block text-[10px] text-emerald-400 mt-1 font-medium">Clocked in today</span>
    </div>

    <!-- Team On Leave -->
    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-6 shadow-xl relative overflow-hidden group hover:border-purple-500/30 transition duration-300">
        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition duration-300 text-purple-400">
            <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
            </svg>
        </div>
        <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Team On Leave</span>
        <span class="block text-4xl font-bold text-slate-100 mt-2">{{ $teamOnLeave }}</span>
        <span class="block text-[10px] text-purple-400 mt-1 font-medium">Approved leaves today</span>
    </div>

    <!-- Pending Approvals -->
    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-6 shadow-xl relative overflow-hidden group hover:border-rose-500/30 transition duration-300">
        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition duration-300 text-rose-400">
            <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
            </svg>
        </div>
        <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Pending Approvals</span>
        <span class="block text-4xl font-bold text-slate-100 mt-2">{{ $pendingApprovals }}</span>
        <span class="block text-[10px] text-rose-400 mt-1 font-medium">Leave requests</span>
    </div>

</div>

<!-- Team Analytics & Calendar Sync -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Team Attendance Summary -->
    <div class="lg:col-span-2 backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-6 shadow-xl">
        <h2 class="text-lg font-bold mb-4 text-slate-100">Team Attendance Analytics</h2>
        <div class="h-64 bg-slate-950/50 rounded-xl border border-white/5 flex items-center justify-center relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-tr from-indigo-500/5 to-purple-500/5"></div>
            <div class="z-10 text-center">
                <svg class="w-12 h-12 text-slate-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.003 9.003 0 1020.945 13H11V3.055z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
                </svg>
                <p class="text-sm font-semibold text-slate-300">Team attendance graphs will automatically generate here</p>
                <p class="text-xs text-slate-500 mt-1">Based on daily clock-ins of direct reports</p>
            </div>
        </div>
    </div>

    <!-- Right Side: Holiday List -->
    <div>
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
    </div>

</div>
@endsection
