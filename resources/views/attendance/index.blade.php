@extends('layouts.app')

@section('title', 'Attendance Sheet')
@section('page_title', 'Daily Attendance Sheet')

@section('content')
<div class="max-w-7xl mx-auto">

    <!-- Top Action -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <p class="text-sm text-slate-400">View and manage daily punch records, worked durations, and shift statuses.</p>
        </div>
        
        <form action="{{ route('attendance.reports.generate') }}" method="GET" class="inline-block">
            <input type="hidden" name="report_type" value="daily">
            <input type="hidden" name="start_date" value="{{ $date }}">
            <input type="hidden" name="end_date" value="{{ $date }}">
            <input type="hidden" name="export" value="csv">
            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-white/5 font-semibold rounded-xl text-xs transition duration-200 cursor-pointer">
                Export CSV
            </button>
        </form>
    </div>

    <!-- Search & Filters -->
    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-6 shadow-2xl mb-6">
        <form action="{{ route('attendance.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label for="search" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Search Employee</label>
                <input type="text" name="search" id="search" value="{{ $search }}" placeholder="Name or code..."
                    class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-2 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm">
            </div>

            <div>
                <label for="department_id" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Department</label>
                <select name="department_id" id="department_id" class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-2 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm select-dark">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ $departmentId == $dept->id ? 'selected' : '' }}>{{ $dept->department_name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="shift_id" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Shift</label>
                <select name="shift_id" id="shift_id" class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-2 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm select-dark">
                    <option value="">All Shifts</option>
                    @foreach($shifts as $sf)
                        <option value="{{ $sf->id }}" {{ $shiftId == $sf->id ? 'selected' : '' }}>{{ $sf->shift_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <div class="flex-grow">
                    <label for="date" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Date</label>
                    <input type="date" name="date" id="date" value="{{ $date }}"
                        class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-2 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm">
                </div>

                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-xs transition duration-200 cursor-pointer">
                    Apply
                </button>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl overflow-hidden shadow-2xl">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-white/5 bg-slate-900/30 text-slate-400 text-xs font-bold uppercase tracking-wider">
                    <th class="px-6 py-4">Employee</th>
                    <th class="px-6 py-4">Department</th>
                    <th class="px-6 py-4">Clock In</th>
                    <th class="px-6 py-4">Clock Out</th>
                    <th class="px-6 py-4 text-center">Worked Hours</th>
                    <th class="px-6 py-4 text-center">Late (Mins)</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5 text-sm text-slate-200">
                @forelse($attendances as $att)
                    <tr class="hover:bg-white/2 transition duration-150">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-800 text-indigo-400 border border-slate-700 flex items-center justify-center font-bold text-xs">
                                    {{ substr($att->user->name, 0, 2) }}
                                </div>
                                <div>
                                    <span class="block font-semibold">{{ $att->user->name }}</span>
                                    <span class="block text-[10px] text-slate-400 font-mono">{{ $att->user->employeeDetail?->employee_code ?? '-' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-slate-300">
                            {{ $att->user->employeeDetail?->department?->department_name ?? '-' }}
                        </td>
                        <td class="px-6 py-4 font-mono text-xs text-slate-300">
                            {{ $att->clock_in ? $att->clock_in->format('h:i A') : '-' }}
                        </td>
                        <td class="px-6 py-4 font-mono text-xs text-slate-300">
                            {{ $att->clock_out ? $att->clock_out->format('h:i A') : '-' }}
                        </td>
                        <td class="px-6 py-4 text-center font-bold font-mono text-indigo-400">
                            {{ $att->worked_hours }} hrs
                        </td>
                        <td class="px-6 py-4 text-center font-mono text-xs {{ $att->late_minutes > 0 ? 'text-rose-400 font-bold' : 'text-slate-400' }}">
                            {{ $att->late_minutes }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg uppercase tracking-wider border 
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
                            <a href="{{ route('attendance.show', $att->id) }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg text-xs font-semibold border border-white/5 transition duration-200 inline-block mr-2 cursor-pointer">
                                View Logs
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-10 text-center text-slate-500">
                            No attendance records recorded for {{ $date }}.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $attendances->appends(request()->all())->links() }}
    </div>

</div>
@endsection
