@extends('layouts.app')

@section('title', 'Team Leave Calendar')
@section('page_title', 'Team Availability Calendar')

@section('content')
@php
    $currentDate = \Carbon\Carbon::create($year, $month, 1);
    $monthName = $currentDate->format('F');
    $daysInMonth = $currentDate->daysInMonth;
    $startOfWeek = $currentDate->dayOfWeek; // 0 (Sun) to 6 (Sat)
    
    // Previous month / year calculations
    $prevMonthDate = (clone $currentDate)->subMonth();
    $nextMonthDate = (clone $currentDate)->addMonth();
    
    // Grid alignment
    $gridDays = [];
    for ($i = 0; $i < $startOfWeek; $i++) {
        $gridDays[] = null;
    }
    for ($i = 1; $i <= $daysInMonth; $i++) {
        $gridDays[] = \Carbon\Carbon::create($year, $month, $i);
    }
    $totalCells = ceil(count($gridDays) / 7) * 7;
    for ($i = count($gridDays); $i < $totalCells; $i++) {
        $gridDays[] = null;
    }
@endphp

<div class="space-y-6">
    <!-- Header & Navigation -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md">
        <div>
            <h2 class="text-xl font-bold text-white">Availability Calendar</h2>
            <p class="text-slate-400 text-sm mt-1">Track leaves, availability, and upcoming corporate holidays for your direct reports.</p>
        </div>
        
        <!-- Month Switcher Controls -->
        <div class="flex items-center gap-2 bg-slate-900 border border-slate-800 rounded-xl p-1.5 self-stretch sm:self-auto justify-between">
            <a href="?month={{ $prevMonthDate->month }}&year={{ $prevMonthDate->year }}" class="p-2 hover:bg-white/5 text-slate-400 hover:text-white rounded-lg transition duration-150">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <span class="text-sm font-semibold text-white px-4">
                {{ $monthName }} {{ $year }}
            </span>
            <a href="?month={{ $nextMonthDate->month }}&year={{ $nextMonthDate->year }}" class="p-2 hover:bg-white/5 text-slate-400 hover:text-white rounded-lg transition duration-150">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
    </div>

    <!-- Main Content Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Calendar Grid -->
        <div class="lg:col-span-3 bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md">
            <!-- Days of Week labels -->
            <div class="grid grid-cols-7 gap-2 text-center mb-4">
                @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $dayLabel)
                    <div class="text-xs font-bold text-slate-500 uppercase tracking-wider py-2">{{ $dayLabel }}</div>
                @endforeach
            </div>

            <!-- Calendar Cells -->
            <div class="grid grid-cols-7 gap-2">
                @foreach($gridDays as $day)
                    @if($day === null)
                        <!-- Empty slot -->
                        <div class="aspect-square bg-slate-950/20 border border-transparent rounded-xl opacity-20"></div>
                    @else
                        @php
                            $dayStr = $day->toDateString();
                            
                            // Check holidays
                            $dayHolidays = $holidays->filter(function($holiday) use ($dayStr) {
                                return \Carbon\Carbon::parse($holiday->holiday_date)->toDateString() === $dayStr;
                            });

                            // Check employee leaves
                            $dayLeaves = $leaves->filter(function($leave) use ($dayStr) {
                                $start = \Carbon\Carbon::parse($leave->start_date)->toDateString();
                                $end = \Carbon\Carbon::parse($leave->end_date)->toDateString();
                                return $dayStr >= $start && $dayStr <= $end;
                            });
                            
                            $isToday = $day->isToday();
                            $isWeekend = $day->isWeekend();
                        @endphp
                        
                        <div class="aspect-square bg-slate-900 border {{ $isToday ? 'border-indigo-500 shadow-md shadow-indigo-500/10' : 'border-slate-800' }} rounded-xl p-2 flex flex-col justify-between hover:bg-slate-800/50 hover:border-slate-700 transition duration-150 relative overflow-hidden group">
                            
                            <!-- Date label -->
                            <div class="flex justify-between items-center z-10">
                                <span class="text-xs font-semibold {{ $isToday ? 'text-indigo-400 font-bold' : ($isWeekend ? 'text-slate-500' : 'text-slate-300') }}">
                                    {{ $day->day }}
                                </span>
                                @if($isToday)
                                    <span class="w-1.5 h-1.5 bg-indigo-500 rounded-full"></span>
                                @endif
                            </div>

                            <!-- Events Container -->
                            <div class="space-y-1 z-10 overflow-y-auto max-h-[70%] custom-scrollbar">
                                <!-- Holiday -->
                                @foreach($dayHolidays as $holiday)
                                    <div class="px-1.5 py-0.5 text-[9px] font-semibold text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 rounded-md truncate cursor-default" title="Holiday: {{ $holiday->holiday_name }}">
                                        🎉 {{ $holiday->holiday_name }}
                                    </div>
                                @endforeach

                                <!-- Leaves -->
                                @foreach($dayLeaves as $leave)
                                    @php
                                        $isApproved = $leave->status === 'approved';
                                        $badgeClass = $isApproved 
                                            ? 'text-indigo-400 bg-indigo-500/10 border-indigo-500/20' 
                                            : 'text-amber-400 bg-amber-500/10 border-amber-500/20';
                                    @endphp
                                    <div class="px-1.5 py-0.5 text-[9px] font-semibold rounded-md border truncate cursor-default {{ $badgeClass }}" title="{{ $leave->employee->name }} ({{ $leave->leaveType->type_name }}) - {{ ucfirst($leave->status) }}">
                                        👤 {{ $leave->employee->name }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        <!-- Right Side Panel: Legend & List -->
        <div class="space-y-6">
            <!-- Calendar Legend -->
            <div class="bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md space-y-4">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider">Calendar Legend</h3>
                
                <div class="space-y-2.5 text-xs text-slate-300">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded bg-emerald-500/10 border border-emerald-500/20 block"></span>
                        <span>Company Holiday</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded bg-indigo-500/10 border border-indigo-500/20 block"></span>
                        <span>Approved Team Leave</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded bg-amber-500/10 border border-amber-500/20 block"></span>
                        <span>Pending Leave Approval</span>
                    </div>
                </div>
            </div>

            <!-- Summary of Leave Requests this Month -->
            <div class="bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md space-y-4">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider">Leaves this Month</h3>
                
                <div class="space-y-3 max-h-[300px] overflow-y-auto custom-scrollbar">
                    @forelse($leaves as $leave)
                        <div class="p-3 bg-slate-900/50 border border-slate-800 rounded-xl space-y-1">
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-semibold text-white">{{ $leave->employee->name }}</span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full border {{ $leave->status === 'approved' ? 'text-indigo-400 bg-indigo-500/10 border-indigo-500/20' : 'text-amber-400 bg-amber-500/10 border-amber-500/20' }}">
                                    {{ ucfirst($leave->status) }}
                                </span>
                            </div>
                            <p class="text-[11px] text-indigo-400 font-medium">{{ $leave->leaveType->type_name }}</p>
                            <p class="text-[10px] text-slate-400 pt-1">
                                {{ \Carbon\Carbon::parse($leave->start_date)->format('M d') }} - {{ \Carbon\Carbon::parse($leave->end_date)->format('M d') }} ({{ $leave->total_days }} {{ Str::plural('day', $leave->total_days) }})
                            </p>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 text-center py-4">No team leaves this month.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 3px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
    }
</style>
@endsection
