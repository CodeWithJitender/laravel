@extends('layouts.app')

@section('title', 'Attendance Reports')
@section('page_title', 'Attendance Report Builder')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Description -->
    <div>
        <p class="text-sm text-slate-400">Query and compile attendance, overtime, late arrival and missed punch reports. Export results directly to CSV format.</p>
    </div>

    <!-- Alert Messages -->
    @if($errors->any())
        <div class="p-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-2xl text-sm">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Builder Form Card -->
    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-6 shadow-2xl">
        <form action="{{ route('attendance.reports.generate') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
            <!-- Report Type -->
            <div>
                <label for="report_type" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Report Category</label>
                <select name="report_type" id="report_type" required
                        class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-2 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm select-dark">
                    <option value="daily" {{ ($type ?? '') == 'daily' ? 'selected' : '' }}>Daily Punches Sheet</option>
                    <option value="missed_punch" {{ ($type ?? '') == 'missed_punch' ? 'selected' : '' }}>Missed Punches List</option>
                    <option value="late" {{ ($type ?? '') == 'late' ? 'selected' : '' }}>Late Comers Summary</option>
                    <option value="overtime" {{ ($type ?? '') == 'overtime' ? 'selected' : '' }}>Overtime (OT) Report</option>
                </select>
            </div>

            <!-- Start Date -->
            <div>
                <label for="start_date" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Start Date</label>
                <input type="date" name="start_date" id="start_date" required 
                       value="{{ $start ?? date('Y-m-d') }}"
                       class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-2 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm">
            </div>

            <!-- End Date -->
            <div>
                <label for="end_date" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">End Date</label>
                <input type="date" name="end_date" id="end_date" required 
                       value="{{ $end ?? date('Y-m-d') }}"
                       class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-2 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm">
            </div>

            <!-- Department -->
            <div>
                <label for="department_id" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Department</label>
                <select name="department_id" id="department_id" 
                        class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-2 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm select-dark">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ ($deptId ?? '') == $dept->id ? 'selected' : '' }}>{{ $dept->department_name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Location -->
            <div>
                <label for="location_id" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Location</label>
                <select name="location_id" id="location_id" 
                        class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-2 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm select-dark">
                    <option value="">All Locations</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ ($locId ?? '') == $loc->id ? 'selected' : '' }}>{{ $loc->location_name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Action Buttons Row -->
            <div class="md:col-span-5 flex justify-end gap-3 border-t border-white/5 pt-4">
                <button type="submit" name="export" value=""
                        class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-white/5 font-semibold rounded-xl text-xs transition duration-200 cursor-pointer">
                    Generate Preview
                </button>
                <button type="submit" name="export" value="csv"
                        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-xs transition duration-200 cursor-pointer flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Export CSV
                </button>
            </div>
        </form>
    </div>

    <!-- Preview Table -->
    @if(isset($results))
        <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl overflow-hidden shadow-2xl">
            <div class="p-6 border-b border-white/5 bg-slate-900/30 flex justify-between items-center">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Previewing {{ count($results) }} Results</h3>
                <span class="text-xs text-slate-400 font-mono">Report: {{ strtoupper($type) }} ({{ $start }} to {{ $end }})</span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-white/5 bg-slate-900/20 text-slate-400 text-xs font-bold uppercase tracking-wider">
                            <th class="px-6 py-4">Date</th>
                            <th class="px-6 py-4">Employee</th>
                            <th class="px-6 py-4">Department</th>
                            <th class="px-6 py-4 font-mono text-center">Clock In</th>
                            <th class="px-6 py-4 font-mono text-center">Clock Out</th>
                            <th class="px-6 py-4 text-center">Worked Hours</th>
                            <th class="px-6 py-4 text-center">Late (Mins)</th>
                            <th class="px-6 py-4 text-center">OT (Mins)</th>
                            <th class="px-6 py-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-sm text-slate-200">
                        @forelse($results as $row)
                            <tr class="hover:bg-white/2 transition duration-150">
                                <td class="px-6 py-4 font-mono text-xs">
                                    {{ $row->attendance_date->format('Y-m-d') }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-800 text-indigo-400 border border-slate-700 flex items-center justify-center font-bold text-xs">
                                            {{ substr($row->user->name, 0, 2) }}
                                        </div>
                                        <div>
                                            <span class="block font-semibold">{{ $row->user->name }}</span>
                                            <span class="block text-[10px] text-slate-400 font-mono">{{ $row->user->employeeDetail?->employee_code ?? '-' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-slate-300">
                                    {{ $row->user->employeeDetail?->department?->department_name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-center font-mono text-xs text-slate-300">
                                    {{ $row->clock_in ? $row->clock_in->format('h:i A') : '-' }}
                                </td>
                                <td class="px-6 py-4 text-center font-mono text-xs text-slate-300">
                                    {{ $row->clock_out ? $row->clock_out->format('h:i A') : '-' }}
                                </td>
                                <td class="px-6 py-4 text-center font-bold font-mono text-indigo-400">
                                    {{ $row->worked_hours ?? '0.00' }} hrs
                                </td>
                                <td class="px-6 py-4 text-center font-mono text-xs {{ $row->late_minutes > 0 ? 'text-rose-400 font-bold' : 'text-slate-400' }}">
                                    {{ $row->late_minutes ?? 0 }}
                                </td>
                                <td class="px-6 py-4 text-center font-mono text-xs {{ $row->overtime_minutes > 0 ? 'text-emerald-400 font-extrabold' : 'text-slate-400' }}">
                                    {{ $row->overtime_minutes ?? 0 }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg uppercase tracking-wider border 
                                        @if($row->attendance_status == 'Present') bg-emerald-500/10 text-emerald-400 border-emerald-500/20
                                        @elseif($row->attendance_status == 'Late') bg-amber-500/10 text-amber-400 border-amber-500/20
                                        @elseif($row->attendance_status == 'Half Day') bg-blue-500/10 text-blue-400 border-blue-500/20
                                        @elseif($row->attendance_status == 'Work From Home') bg-purple-500/10 text-purple-400 border-purple-500/20
                                        @elseif($row->attendance_status == 'Missed Punch') bg-orange-500/10 text-orange-400 border-orange-500/20
                                        @else bg-rose-500/10 text-rose-400 border-rose-500/20
                                        @endif">
                                        {{ $row->attendance_status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-10 text-center text-slate-500">
                                    No records found matching filters for query duration.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form[action="{{ route('attendance.reports.generate') }}"]');
    if (form) {
        form.addEventListener('submit', (e) => {
            const startVal = document.getElementById('start_date').value;
            const endVal = document.getElementById('end_date').value;
            if (startVal && endVal) {
                const start = new Date(startVal);
                const end = new Date(endVal);
                if (end < start) {
                    e.preventDefault();
                    alert('End Date must be greater than or equal to Start Date.');
                }
            }
        });
    }
});
</script>
@endsection
