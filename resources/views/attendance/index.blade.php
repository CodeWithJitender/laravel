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
        
        <div class="flex items-center gap-2">
            @can('attendance.create')
            <button type="button" onclick="document.getElementById('importModal').classList.remove('hidden')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-xs transition duration-200 cursor-pointer">
                Import Attendance
            </button>
            @endcan

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
    </div>

    <!-- Import errors -->
    @if(session('import_errors'))
        <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-sm">
            <h4 class="font-bold mb-2">Import Errors occurred:</h4>
            <ul class="list-disc list-inside space-y-1">
                @foreach(session('import_errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

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
                                    {{ substr($att->user?->name ?? 'N/A', 0, 2) }}
                                </div>
                                <div>
                                    <span class="block font-semibold">{{ $att->user?->name ?? 'Deleted User' }}</span>
                                    <span class="block text-[10px] text-slate-400 font-mono">{{ $att->user?->employeeDetail?->employee_code ?? '-' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-slate-300">
                            {{ $att->user?->employeeDetail?->department?->department_name ?? '-' }}
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

    <!-- Import Modal -->
    <div id="importModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/60 backdrop-blur-sm">
        <div class="w-full max-w-md p-6 backdrop-blur-md bg-slate-900 border border-white/10 rounded-3xl shadow-2xl relative">
            <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="absolute top-4 right-4 text-slate-400 hover:text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <h3 class="text-lg font-bold text-slate-200 mb-2">Import Attendance Sheet</h3>
            <p class="text-xs text-slate-400 mb-4">Upload a CSV file containing daily punch records for employees. The file columns must match the required template.</p>
            
            <div class="mb-4">
                <a href="{{ route('attendance.import.template') }}" class="inline-flex items-center text-xs font-semibold text-indigo-400 hover:text-indigo-300 transition duration-200">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Download CSV Template
                </a>
            </div>

            <!-- CSV Format Reference -->
            <div class="mb-5 p-4 rounded-2xl bg-white/5 border border-white/5 text-[10px] text-slate-300 space-y-2.5">
                <p class="font-bold uppercase tracking-wider text-slate-400 text-[9px]">Required CSV Format & Fields:</p>
                <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-slate-400 border-t border-white/5 pt-2">
                    <div>
                        <span class="font-mono text-indigo-400 font-semibold block">Employee Email</span>
                        <p class="text-[8px] leading-relaxed text-slate-500">e.g. user@company.com (or blank if Code is given)</p>
                    </div>
                    <div>
                        <span class="font-mono text-indigo-400 font-semibold block">Employee Code</span>
                        <p class="text-[8px] leading-relaxed text-slate-500">e.g. EMP-001 (or blank if Email is given)</p>
                    </div>
                    <div>
                        <span class="font-mono text-indigo-400 font-semibold block">Date</span>
                        <p class="text-[8px] leading-relaxed text-slate-500">Format: YYYY-MM-DD</p>
                    </div>
                    <div>
                        <span class="font-mono text-indigo-400 font-semibold block">Clock In / Out</span>
                        <p class="text-[8px] leading-relaxed text-slate-500">Format: YYYY-MM-DD HH:MM:SS (optional)</p>
                    </div>
                </div>
                <div class="border-t border-white/5 pt-2 leading-relaxed">
                    <span class="font-mono text-indigo-400 font-semibold block mb-0.5">Status Values:</span>
                    <span class="text-[8px] text-slate-400">Present, Absent, Late, Half Day, Work From Home, Holiday, Weekly Off, On Leave, Missed Punch</span>
                </div>
            </div>

            <form action="{{ route('attendance.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Select CSV File</label>
                    <input type="file" name="file" required accept=".csv" class="w-full text-slate-300 text-sm bg-slate-950/60 border border-white/10 rounded-2xl p-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold rounded-xl text-xs transition duration-200 cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-xs transition duration-200 cursor-pointer">
                        Upload & Import
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
