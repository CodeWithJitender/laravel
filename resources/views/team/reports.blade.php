@extends('layouts.app')

@section('title', 'Team Reports')
@section('page_title', 'Team Analytics & Reports')

@section('content')
<div class="space-y-6">
    <!-- Header & Period Filter -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md">
        <div>
            <h2 class="text-xl font-bold text-white">Team Performance & Compliance reports</h2>
            <p class="text-slate-400 text-sm mt-1">Review clock-in compliance, leave consumption metrics, and work hour averages.</p>
        </div>

        <form action="/team-reports" method="GET" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <div class="flex gap-2">
                <!-- Month Filter -->
                <select name="month" class="bg-slate-900 border border-slate-800 rounded-xl px-4 py-2 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                        </option>
                    @endfor
                </select>
                
                <!-- Year Filter -->
                <select name="year" class="bg-slate-900 border border-slate-800 rounded-xl px-4 py-2 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
                    @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endfor
                </select>
            </div>

            <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-semibold transition cursor-pointer">
                Apply Filter
            </button>
        </form>
    </div>

    <!-- Analytics Dashboard Overview Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Metric Card 1: Late Incidents Count -->
        <div class="bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md relative overflow-hidden">
            <div class="absolute top-[-20%] right-[-10%] w-[120px] h-[120px] rounded-full bg-red-500/5 blur-[30px] pointer-events-none"></div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest block">Late Clock-Ins</span>
            <div class="flex items-baseline gap-2 mt-4">
                <span class="text-4xl font-extrabold text-white">{{ $lateReport->count() }}</span>
                <span class="text-xs text-slate-400">incidents this month</span>
            </div>
            <div class="text-xs text-slate-500 mt-2">Compliance audit metric</div>
        </div>

        <!-- Metric Card 2: Approved Leave Days -->
        <div class="bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md relative overflow-hidden">
            <div class="absolute top-[-20%] right-[-10%] w-[120px] h-[120px] rounded-full bg-indigo-500/5 blur-[30px] pointer-events-none"></div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest block">Leaves Consumed</span>
            <div class="flex items-baseline gap-2 mt-4">
                <span class="text-4xl font-extrabold text-white">{{ $leaveSummary->sum('total_days_taken') }}</span>
                <span class="text-xs text-slate-400">total days taken</span>
            </div>
            <div class="text-xs text-slate-500 mt-2">Sum of approved leave days</div>
        </div>

        <!-- Metric Card 3: Avg Daily Working Hours -->
        <div class="bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md relative overflow-hidden">
            <div class="absolute top-[-20%] right-[-10%] w-[120px] h-[120px] rounded-full bg-emerald-500/5 blur-[30px] pointer-events-none"></div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest block">Average Work Hours</span>
            <div class="flex items-baseline gap-2 mt-4">
                <span class="text-4xl font-extrabold text-white">
                    {{ $hoursSummary->count() > 0 ? number_format($hoursSummary->avg('avg_hours'), 1) : '0.0' }}
                </span>
                <span class="text-xs text-slate-400">hours / day</span>
            </div>
            <div class="text-xs text-slate-500 mt-2">Team attendance average</div>
        </div>
    </div>

    <!-- Detailed Reports Section -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <!-- Report 1: Late Arrivals Log -->
        <div class="bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md space-y-4">
            <div class="flex justify-between items-center border-b border-white/5 pb-3">
                <h3 class="text-base font-bold text-white">Late Clock-In Compliance</h3>
                <span class="text-[10px] px-2 py-0.5 rounded-md bg-red-500/10 text-red-400 border border-red-500/20 font-semibold uppercase">Log</span>
            </div>

            <div class="overflow-x-auto max-h-[400px] overflow-y-auto custom-scrollbar">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="text-slate-400 font-bold uppercase tracking-wider border-b border-white/5">
                            <th class="pb-3 pr-2">Employee</th>
                            <th class="pb-3 px-2">Date</th>
                            <th class="pb-3 px-2">Clock In</th>
                            <th class="pb-3 pl-2 text-right">Late Minutes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-slate-300">
                        @forelse($lateReport as $late)
                            <tr class="hover:bg-white/[0.01]">
                                <td class="py-2.5 pr-2 font-semibold text-white">{{ $late->user->name }}</td>
                                <td class="py-2.5 px-2">{{ $late->attendance_date->format('M d, Y') }}</td>
                                <td class="py-2.5 px-2 text-slate-400">{{ $late->clock_in ? \Carbon\Carbon::parse($late->clock_in)->format('h:i A') : 'N/A' }}</td>
                                <td class="py-2.5 pl-2 text-right text-rose-400 font-semibold">{{ $late->late_minutes }}m</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-500">No late clock-ins logged this month.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Report 2: Average Daily Working Hours -->
        <div class="bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md space-y-4">
            <div class="flex justify-between items-center border-b border-white/5 pb-3">
                <h3 class="text-base font-bold text-white">Average Daily Working Hours</h3>
                <span class="text-[10px] px-2 py-0.5 rounded-md bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-semibold uppercase">Productivity</span>
            </div>

            <div class="overflow-x-auto max-h-[400px] overflow-y-auto custom-scrollbar">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="text-slate-400 font-bold uppercase tracking-wider border-b border-white/5">
                            <th class="pb-3 pr-2">Employee</th>
                            <th class="pb-3 pl-2 text-right">Avg Work Hours / Day</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-slate-300">
                        @forelse($hoursSummary as $hrs)
                            <tr class="hover:bg-white/[0.01]">
                                <td class="py-3 pr-2 font-semibold text-white">{{ $hrs->user->name }}</td>
                                <td class="py-3 pl-2 text-right text-emerald-400 font-bold text-sm">{{ number_format($hrs->avg_hours, 2) }} hrs</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="py-8 text-center text-slate-500">No active work logs recorded.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Report 3: Monthly Leave Summaries -->
        <div class="bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md space-y-4 xl:col-span-2">
            <div class="flex justify-between items-center border-b border-white/5 pb-3">
                <h3 class="text-base font-bold text-white">Leave Summary consumption</h3>
                <span class="text-[10px] px-2 py-0.5 rounded-md bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 font-semibold uppercase">Time-Off</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="text-slate-400 font-bold uppercase tracking-wider border-b border-white/5">
                            <th class="pb-3 pr-2">Employee</th>
                            <th class="pb-3 px-2">Employee Code</th>
                            <th class="pb-3 pl-2 text-right">Total Approved Leave Days</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-slate-300">
                        @forelse($leaveSummary as $lv)
                            <tr class="hover:bg-white/[0.01]">
                                <td class="py-3 pr-2 font-semibold text-white">{{ $lv->employee->name }}</td>
                                <td class="py-3 px-2 text-slate-400">{{ $lv->employee->employeeDetail?->employee_code ?? 'N/A' }}</td>
                                <td class="py-3 pl-2 text-right text-indigo-400 font-bold">{{ $lv->total_days_taken }} days</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-8 text-center text-slate-500">No approved leave requests taken this month.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
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
