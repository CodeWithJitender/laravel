@extends('layouts.app')

@section('title', 'Attendance Details')
@section('page_title', 'Attendance Details')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Back Button & Title -->
    <div class="flex items-center justify-between mb-6">
        <a href="{{ auth()->user()->hasRole('Employee') ? route('attendance.my_history') : route('attendance.index') }}" 
           class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-white/5 font-semibold rounded-xl text-xs transition duration-200 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Attendance
        </a>

        @can('attendance.correction.request')
            @if(auth()->id() === $attendance->user_id)
                <a href="{{ route('attendance.corrections.create', ['date' => $attendance->attendance_date->toDateString()]) }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-xs transition duration-200 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Request Correction
                </a>
            @endif
        @endcan
    </div>

    <!-- Main Card Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- Profile info & Status -->
        <div class="md:col-span-2 backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-6 shadow-2xl">
            <div class="flex items-center gap-4 mb-6 border-b border-white/5 pb-6">
                <div class="w-16 h-16 rounded-2xl bg-slate-800 text-indigo-400 border border-slate-700 flex items-center justify-center font-bold text-xl shadow-lg">
                    {{ substr($attendance->user->name, 0, 2) }}
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-100">{{ $attendance->user->name }}</h2>
                    <p class="text-sm text-slate-400 font-mono mt-0.5">{{ $attendance->user->employeeDetail?->employee_code ?? '-' }} &bull; {{ $attendance->user->employeeDetail?->department?->department_name ?? '-' }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-6">
                <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Date</span>
                    <span class="text-slate-200 font-semibold">{{ $attendance->attendance_date->format('M d, Y') }}</span>
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Status</span>
                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg uppercase tracking-wider border 
                        @if($attendance->attendance_status == 'Present') bg-emerald-500/10 text-emerald-400 border-emerald-500/20
                        @elseif($attendance->attendance_status == 'Late') bg-amber-500/10 text-amber-400 border-amber-500/20
                        @elseif($attendance->attendance_status == 'Half Day') bg-blue-500/10 text-blue-400 border-blue-500/20
                        @elseif($attendance->attendance_status == 'Work From Home') bg-purple-500/10 text-purple-400 border-purple-500/20
                        @elseif($attendance->attendance_status == 'Missed Punch') bg-orange-500/10 text-orange-400 border-orange-500/20
                        @else bg-rose-500/10 text-rose-400 border-rose-500/20
                        @endif">
                        {{ $attendance->attendance_status }}
                    </span>
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Shift Type</span>
                    <span class="text-indigo-400 font-semibold">{{ $attendance->shift?->shift_name ?? 'Default' }}</span>
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Clock In</span>
                    <span class="text-slate-200 font-mono font-semibold">{{ $attendance->clock_in ? $attendance->clock_in->format('h:i A') : '-' }}</span>
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Clock Out</span>
                    <span class="text-slate-200 font-mono font-semibold">{{ $attendance->clock_out ? $attendance->clock_out->format('h:i A') : '-' }}</span>
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Shift Timing</span>
                    <span class="text-slate-400 text-xs font-mono">
                        {{ $attendance->shift ? $attendance->shift->start_time . ' - ' . $attendance->shift->end_time : '-' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Calculated Metrics -->
        <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-6 shadow-2xl flex flex-col justify-between">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 border-b border-white/5 pb-2">Calculated Metrics</h3>
            <div class="space-y-4 flex-grow">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-400">Worked Hours</span>
                    <span class="text-lg font-bold font-mono text-indigo-400">{{ $attendance->worked_hours ?? '0.00' }} hrs</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-400">Late Minutes</span>
                    <span class="text-sm font-bold font-mono {{ $attendance->late_minutes > 0 ? 'text-amber-400' : 'text-slate-400' }}">{{ $attendance->late_minutes ?? 0 }} mins</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-400">Early Exit</span>
                    <span class="text-sm font-bold font-mono {{ $attendance->early_exit_minutes > 0 ? 'text-orange-400' : 'text-slate-400' }}">{{ $attendance->early_exit_minutes ?? 0 }} mins</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-400">Overtime</span>
                    <span class="text-sm font-bold font-mono {{ $attendance->overtime_minutes > 0 ? 'text-emerald-400 font-extrabold' : 'text-slate-400' }}">{{ $attendance->overtime_minutes ?? 0 }} mins</span>
                </div>
            </div>
            
            @if($attendance->remarks)
                <div class="mt-4 pt-4 border-t border-white/5">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Remarks</span>
                    <p class="text-xs text-slate-300 italic">"{{ $attendance->remarks }}"</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Punch Log Audit Trail -->
    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-6 shadow-2xl mb-6">
        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Detailed Punch Log & Audit Trail</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-white/5 text-slate-400 text-xs font-bold uppercase tracking-wider">
                        <th class="py-3 px-4">Punch Time</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4">Method</th>
                        <th class="py-3 px-4">IP Address</th>
                        <th class="py-3 px-4">Device Info</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-sm text-slate-300">
                    @forelse($attendance->logs as $log)
                        <tr class="hover:bg-white/2 transition duration-150">
                            <td class="py-3 px-4 font-mono text-xs text-slate-200">
                                {{ $log->log_time->format('Y-m-d h:i:s A') }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-lg uppercase tracking-wider border 
                                    @if($log->type == 'in') bg-emerald-500/10 text-emerald-400 border-emerald-500/20
                                    @else bg-rose-500/10 text-rose-400 border-rose-500/20
                                    @endif">
                                    Clock {{ strtoupper($log->type) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 uppercase text-xs font-semibold">
                                {{ $log->method }}
                            </td>
                            <td class="py-3 px-4 font-mono text-xs">
                                {{ $log->ip_address }}
                            </td>
                            <td class="py-3 px-4 text-xs text-slate-400 truncate max-w-xs" title="{{ $log->device_info }}">
                                {{ $log->device_info }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-500">
                                No raw punch logs found for this day's attendance.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
