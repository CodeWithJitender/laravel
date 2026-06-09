@extends('layouts.app')

@section('title', 'Attendance History')
@section('page_title', 'My Attendance Logs')

@section('content')
<div class="max-w-6xl mx-auto">

    <!-- Filters & Stats Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <form action="{{ route('attendance.my_history') }}" method="GET" class="flex items-center gap-3">
            <select name="month" class="bg-slate-900 border border-white/10 rounded-xl py-2 px-3 text-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 select-dark">
                @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                @endforeach
            </select>

            <select name="year" class="bg-slate-900 border border-white/10 rounded-xl py-2 px-3 text-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 select-dark">
                @foreach(range(2025, 2028) as $y)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>

            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-xs transition duration-200 cursor-pointer">
                Filter
            </button>
        </form>
        
        <div class="text-xs text-slate-400">
            Current Date: {{ date('F d, Y') }}
        </div>
    </div>

    <!-- Monthly Summary Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4 mb-6">
        <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-4 shadow-xl text-center">
            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Present</span>
            <span class="text-xl font-bold text-emerald-400">{{ $summary->present_days ?? 0 }}</span>
        </div>
        <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-4 shadow-xl text-center">
            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Late Days</span>
            <span class="text-xl font-bold text-amber-400">{{ $summary->late_days ?? 0 }}</span>
        </div>
        <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-4 shadow-xl text-center">
            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Absent</span>
            <span class="text-xl font-bold text-rose-400">{{ $summary->absent_days ?? 0 }}</span>
        </div>
        <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-4 shadow-xl text-center">
            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">WFH</span>
            <span class="text-xl font-bold text-purple-400">{{ $summary->wfh_days ?? 0 }}</span>
        </div>
        <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-4 shadow-xl text-center">
            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Leaves</span>
            <span class="text-xl font-bold text-blue-400">{{ $summary->leave_days ?? 0 }}</span>
        </div>
        <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-4 shadow-xl text-center">
            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Missed Punch</span>
            <span class="text-xl font-bold text-orange-400">{{ $summary->missed_punch_days ?? 0 }}</span>
        </div>
        <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-4 shadow-xl text-center">
            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Working Hours</span>
            <span class="text-xl font-bold text-white font-mono">{{ $summary->total_working_hours ?? '0.00' }}</span>
        </div>
        <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-4 shadow-xl text-center">
            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Overtime</span>
            <span class="text-xl font-bold text-indigo-400 font-mono">{{ $summary->total_overtime_hours ?? '0.00' }}</span>
        </div>
    </div>

    <!-- View Mode Tabs -->
    <div class="flex items-center gap-2 mb-6 border-b border-white/10 pb-4">
        <a href="{{ route('attendance.my_history', ['month' => $month, 'year' => $year]) }}" class="px-4 py-2.5 text-xs font-semibold rounded-xl border border-white/5 transition duration-150 {{ !request()->has('view') || request()->get('view') !== 'list' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/15 border-indigo-500/10' : 'bg-slate-900/50 text-slate-400 hover:text-slate-255' }}">
            Calendar View
        </a>
        <a href="{{ route('attendance.my_history', ['month' => $month, 'year' => $year, 'view' => 'list']) }}" class="px-4 py-2.5 text-xs font-semibold rounded-xl border border-white/5 transition duration-150 {{ request()->get('view') === 'list' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/15 border-indigo-500/10' : 'bg-slate-900/50 text-slate-400 hover:text-slate-255' }}">
            Punch History List
        </a>
    </div>

    @if(isset($history))
        <!-- Punch History List Log Card -->
        <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-6 shadow-2xl relative overflow-hidden">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-6">Punch History Log</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-white/5 bg-slate-900/10 text-slate-400 text-xs font-bold uppercase tracking-wider">
                            <th class="px-6 py-4">Date</th>
                            <th class="px-6 py-4">Shift</th>
                            <th class="px-6 py-4">Clock In</th>
                            <th class="px-6 py-4">Clock Out</th>
                            <th class="px-6 py-4 text-center">Worked Hours</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-sm text-slate-200">
                        @forelse($history as $att)
                            <tr class="hover:bg-white/2 transition duration-150">
                                <td class="px-6 py-4 font-semibold">
                                    {{ $att->attendance_date->format('M d, Y') }} ({{ $att->attendance_date->format('l') }})
                                </td>
                                <td class="px-6 py-4 text-slate-300">
                                    {{ $att->shift?->shift_name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 font-mono text-xs">
                                    {{ $att->clock_in ? $att->clock_in->format('h:i A') : '-' }}
                                </td>
                                <td class="px-6 py-4 font-mono text-xs">
                                    {{ $att->clock_out ? $att->clock_out->format('h:i A') : '-' }}
                                </td>
                                <td class="px-6 py-4 text-center font-mono text-indigo-400 font-bold">
                                    {{ $att->worked_hours ?? '0.00' }} hrs
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-lg uppercase tracking-wider border 
                                        @if($att->attendance_status == 'Present') bg-emerald-500/10 text-emerald-400 border-emerald-500/20
                                        @elseif($att->attendance_status == 'Late') bg-amber-500/10 text-amber-400 border-amber-500/20
                                        @elseif($att->attendance_status == 'Half Day') bg-blue-500/10 text-blue-400 border-blue-500/20
                                        @elseif($att->attendance_status == 'Work From Home') bg-purple-500/10 text-purple-400 border-purple-500/20
                                        @elseif($att->attendance_status == 'Missed Punch') bg-orange-500/10 text-orange-400 border-orange-500/20
                                        @else bg-rose-500/10 text-rose-400 border-rose-500/20
                                        @endif">
                                        {{ $att->attendance_status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('attendance.corrections.create', ['date' => $att->attendance_date->toDateString()]) }}" 
                                       class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-indigo-400 border border-white/5 rounded-lg text-xs font-semibold transition cursor-pointer inline-block">
                                        Request Fix
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-slate-500">
                                    No punch records found for this period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($history->hasPages())
                <div class="mt-6">
                    {{ $history->links() }}
                </div>
            @endif
        </div>
    @else
        <!-- Calendar Card -->
        <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-6 shadow-2xl relative overflow-hidden">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-6 font-semibold">Calendar Logs</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7 gap-4">
                @foreach($calendar as $dateString => $att)
                    @php
                        $carbonDate = \Carbon\Carbon::parse($dateString);
                    @endphp
                    <div class="p-4 rounded-2xl border bg-slate-900/40 hover:bg-slate-900/60 transition duration-150 flex flex-col justify-between h-36 
                        {{ $carbonDate->isToday() ? 'border-indigo-500/50 shadow-lg shadow-indigo-500/5' : 'border-white/5' }}">
                        
                        <div class="flex justify-between items-start">
                            <span class="text-xs font-mono font-bold {{ $carbonDate->isToday() ? 'text-indigo-400' : 'text-slate-400' }}">
                                {{ $carbonDate->format('d') }} ({{ substr($carbonDate->format('D'), 0, 3) }})
                            </span>
                            
                            @if($att)
                                <span class="w-2.5 h-2.5 rounded-full 
                                    @if($att->attendance_status == 'Present') bg-emerald-500
                                    @elseif($att->attendance_status == 'Late') bg-amber-500
                                    @elseif($att->attendance_status == 'Half Day') bg-blue-500
                                    @elseif($att->attendance_status == 'Work From Home') bg-purple-500
                                    @elseif($att->attendance_status == 'Missed Punch') bg-orange-500
                                    @else bg-rose-500
                                    @endif" 
                                    title="Status: {{ $att->attendance_status }}"></span>
                            @elseif($carbonDate->isAfter(today()))
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500" title="Status: Upcoming"></span>
                            @endif
                        </div>

                        <div class="my-2 flex-grow">
                            @if($att)
                                @if($att->clock_in)
                                    <div class="text-[10px] text-slate-300 font-mono">In: {{ $att->clock_in->format('h:i A') }}</div>
                                @endif
                                @if($att->clock_out)
                                    <div class="text-[10px] text-slate-300 font-mono">Out: {{ $att->clock_out->format('h:i A') }}</div>
                                    <div class="text-[9px] text-indigo-400 font-bold font-mono mt-1">{{ $att->worked_hours }} hrs</div>
                                @else
                                    @if($att->clock_in)
                                        <div class="text-[10px] text-orange-400 font-medium italic mt-1">Punch Pending</div>
                                    @endif
                                @endif
                            @else
                                @if($carbonDate->isAfter(today()))
                                    <div class="text-[10px] text-slate-500 italic mt-2">Scheduled</div>
                                @else
                                    <div class="text-[10px] text-slate-500 italic mt-2">No Punch Recorded</div>
                                @endif
                            @endif
                        </div>

                        <div class="pt-1.5 border-t border-white/5 flex justify-between items-center text-[10px]">
                            @if($att)
                                <span class="font-bold uppercase tracking-wider text-[9px] 
                                    @if($att->attendance_status == 'Present') text-emerald-400
                                    @elseif($att->attendance_status == 'Late') text-amber-400
                                    @elseif($att->attendance_status == 'Half Day') text-blue-400
                                    @elseif($att->attendance_status == 'Work From Home') text-purple-400
                                    @elseif($att->attendance_status == 'Missed Punch') text-orange-400
                                    @else text-rose-400
                                    @endif">
                                    {{ $att->attendance_status }}
                                </span>
                            @else
                                @if($carbonDate->isAfter(today()))
                                    <span class="text-emerald-400 uppercase tracking-wider font-bold text-[9px]">Upcoming</span>
                                @else
                                    <span class="text-rose-400 uppercase tracking-wider font-bold text-[9px]">Absent</span>
                                @endif
                            @endif

                            @if(!$carbonDate->isAfter(today()))
                                <a href="{{ route('attendance.corrections.create', ['date' => $dateString]) }}" 
                                    class="text-indigo-400 hover:text-indigo-300 font-semibold cursor-pointer">
                                    Fix
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection
