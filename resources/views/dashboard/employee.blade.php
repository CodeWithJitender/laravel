@extends('layouts.app')

@section('title', 'Employee Dashboard')
@section('page_title', 'My Dashboard')

@section('content')
<!-- Alert Messages -->
@if(session('success'))
    <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-2xl text-sm shadow-sm animate-pulse">
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="mb-6 p-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-2xl text-sm shadow-sm">
        {{ session('error') }}
    </div>
@endif

<!-- Top Section: Welcome Banner with Clock-in Widget -->
<div class="backdrop-blur-md bg-gradient-to-r from-slate-900 via-indigo-950/40 to-slate-900 border border-white/10 rounded-3xl p-8 mb-8 relative overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-6 shadow-2xl">
    <div class="absolute inset-0 bg-gradient-to-tr from-indigo-500/5 to-purple-500/5"></div>
    <div class="z-10">
        <h2 class="text-2xl font-bold text-slate-100">Welcome back, {{ auth()->user()->name }}!</h2>
        <p class="text-slate-400 text-sm mt-1">Have a productive day at {{ auth()->user()->employeeDetail?->location?->location_name ?? 'Office' }}!</p>
        
        <div class="flex flex-wrap gap-3 mt-4 text-xs font-semibold text-slate-400">
            <span class="px-3 py-1 bg-slate-800/80 rounded-lg border border-white/5">Code: {{ auth()->user()->employeeDetail?->employee_code }}</span>
            <span class="px-3 py-1 bg-slate-800/80 rounded-lg border border-white/5">Dept: {{ auth()->user()->employeeDetail?->department?->department_name }}</span>
            <span class="px-3 py-1 bg-slate-800/80 rounded-lg border border-white/5">Role: {{ auth()->user()->employeeDetail?->designation?->designation_name }}</span>
        </div>
    </div>
    
    <!-- Quick Web Clock-In Widget -->
    @if($activeSession)
        <form action="{{ route('attendance.clock_out') }}" method="POST" class="z-10 shrink-0 bg-slate-950/80 border border-white/10 rounded-2xl p-5 w-full md:w-64 flex flex-col items-center">
            @csrf
            <span class="text-xs uppercase font-bold tracking-wider text-slate-500">Live Time</span>
            <span id="liveTime" class="text-2xl font-bold text-slate-200 mt-1">00:00:00 AM</span>
            <span class="text-[10px] text-slate-400 mt-1">Clocked In: {{ $activeSession->clock_in->format('h:i A') }}</span>
            <input type="text" name="remarks" placeholder="Clock out remarks..." class="w-full bg-slate-900 border border-white/10 rounded-lg px-2.5 py-1 text-[10px] text-slate-200 mt-2 focus:ring-1 focus:ring-indigo-500 outline-none">
            <button type="submit" class="w-full py-2 bg-gradient-to-r from-rose-500 to-rose-600 hover:from-rose-600 hover:to-rose-700 text-white font-medium rounded-xl text-xs mt-3 shadow-lg shadow-rose-500/20 transition duration-200 transform hover:-translate-y-0.5 cursor-pointer">
                Clock Out
            </button>
        </form>
    @elseif($clockedOutToday)
        <div class="z-10 shrink-0 bg-slate-950/80 border border-white/10 rounded-2xl p-5 w-full md:w-64 flex flex-col items-center">
            <span class="text-xs uppercase font-bold tracking-wider text-slate-500">Live Time</span>
            <span id="liveTime" class="text-2xl font-bold text-slate-200 mt-1">00:00:00 AM</span>
            <span class="text-xs font-semibold text-emerald-400 mt-3 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Shift Completed Today
            </span>
        </div>
    @else
        <form action="{{ route('attendance.clock_in') }}" method="POST" class="z-10 shrink-0 bg-slate-950/80 border border-white/10 rounded-2xl p-5 w-full md:w-64 flex flex-col items-center">
            @csrf
            <span class="text-xs uppercase font-bold tracking-wider text-slate-500">Live Time</span>
            <span id="liveTime" class="text-2xl font-bold text-slate-200 mt-1">00:00:00 AM</span>
            <input type="text" name="remarks" placeholder="Clock in remarks..." class="w-full bg-slate-900 border border-white/10 rounded-lg px-2.5 py-1 text-[10px] text-slate-200 mt-2 focus:ring-1 focus:ring-indigo-500 outline-none">
            <button type="submit" class="w-full py-2 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-medium rounded-xl text-xs mt-3 shadow-lg shadow-emerald-500/20 transition duration-200 transform hover:-translate-y-0.5 cursor-pointer">
                Clock In
            </button>
        </form>
    @endif
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    
    <!-- Attendance card -->
    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-6 shadow-xl relative overflow-hidden">
        <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">This Month Attendance</span>
        <div class="grid grid-cols-3 gap-2 mt-4 text-center">
            <div class="bg-slate-900/60 p-3 rounded-xl border border-white/5">
                <span class="block text-sm font-bold text-slate-100">{{ $attendanceSummary['present'] }}</span>
                <span class="block text-[9px] uppercase font-bold text-slate-500 mt-0.5">Present</span>
            </div>
            <div class="bg-slate-900/60 p-3 rounded-xl border border-white/5">
                <span class="block text-sm font-bold text-amber-400">{{ $attendanceSummary['late'] }}</span>
                <span class="block text-[9px] uppercase font-bold text-slate-500 mt-0.5">Late</span>
            </div>
            <div class="bg-slate-900/60 p-3 rounded-xl border border-white/5">
                <span class="block text-sm font-bold text-rose-400">{{ $attendanceSummary['absent'] }}</span>
                <span class="block text-[9px] uppercase font-bold text-slate-500 mt-0.5">Absent</span>
            </div>
        </div>
    </div>

    <!-- Leave Balance Card -->
    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-6 shadow-xl">
        <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Leave Balance (Days)</span>
        <div class="grid grid-cols-3 gap-2 mt-4 text-center">
            <div class="bg-slate-900/60 p-3 rounded-xl border border-white/5">
                <span class="block text-sm font-bold text-slate-100">{{ number_format($leaveBalance['allocated'], 1) }}</span>
                <span class="block text-[9px] uppercase font-bold text-slate-500 mt-0.5">Allocated</span>
            </div>
            <div class="bg-slate-900/60 p-3 rounded-xl border border-white/5">
                <span class="block text-sm font-bold text-indigo-400">{{ number_format($leaveBalance['used'], 1) }}</span>
                <span class="block text-[9px] uppercase font-bold text-slate-500 mt-0.5">Used</span>
            </div>
            <div class="bg-slate-900/60 p-3 rounded-xl border border-white/5">
                <span class="block text-sm font-bold text-emerald-400">{{ number_format($leaveBalance['remaining'], 1) }}</span>
                <span class="block text-[9px] uppercase font-bold text-slate-500 mt-0.5">Remaining</span>
            </div>
        </div>
    </div>

    <!-- Latest Registry Status -->
    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-6 shadow-xl flex flex-col justify-between">
        <div>
            <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">Registry Summary</span>
            <div class="space-y-2.5">
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-400">Hours worked (Month):</span>
                    <span class="font-bold font-mono text-indigo-400">{{ number_format($workingHoursSummary, 1) }} hrs</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-400">Pending leaves:</span>
                    <span class="font-bold text-amber-400">{{ $pendingLeaveRequestsCount }}</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-400">Latest Payslip:</span>
                    @if($latestPayslip)
                        <a href="{{ route('payslips.show', $latestPayslip->id) }}" class="font-bold text-emerald-400 hover:underline text-right font-mono">
                            ₹{{ number_format($latestPayslip->net_salary, 2) }}
                        </a>
                    @else
                        <span class="text-slate-500 font-semibold text-right">N/A</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Holidays -->
    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-6 shadow-xl flex flex-col justify-between">
        <div>
            <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">Next Holidays</span>
            <div class="space-y-2">
                @foreach($upcomingHolidays as $holiday)
                    <div class="flex items-center justify-between p-2 bg-slate-900/60 rounded-xl border border-white/5">
                        <div>
                            <span class="block text-[10px] font-bold text-slate-200 truncate max-w-[120px]">{{ $holiday['name'] }}</span>
                            <span class="block text-[8px] text-slate-400">{{ $holiday['date'] }}</span>
                        </div>
                        <span class="text-[8px] font-bold px-1.5 py-0.5 bg-indigo-500/10 text-indigo-400 rounded-md">
                            {{ $holiday['days_left'] }} days
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

</div>

<!-- Lower grid: Announcements & Leave Request Log -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    
    <!-- Announcements -->
    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-6 shadow-xl">
        <h3 class="text-base font-bold text-slate-100 mb-4">Announcements</h3>
        <div class="space-y-4">
            @foreach($announcements as $announcement)
                <div class="p-4 bg-slate-900/60 rounded-xl border border-white/5">
                    <span class="block text-sm font-semibold text-indigo-400">{{ $announcement['title'] }}</span>
                    <p class="text-xs text-slate-300 mt-2 leading-relaxed">{{ $announcement['content'] }}</p>
                    <span class="block text-[10px] text-slate-500 mt-3 text-right">{{ $announcement['date'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Recent Leave Requests -->
    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-6 shadow-xl">
        <h3 class="text-base font-bold text-slate-100 mb-4">My Leave History</h3>
        <div class="space-y-3">
            @foreach($leaveRequests as $req)
                <div class="flex items-center justify-between p-3.5 bg-slate-900/60 rounded-xl border border-white/5">
                    <div>
                        <span class="block text-xs font-bold text-slate-200">{{ $req['type'] }}</span>
                        <span class="block text-[10px] text-slate-400 mt-0.5">{{ $req['duration'] }}</span>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 bg-emerald-500/10 text-emerald-400 rounded-full border border-emerald-500/10">
                        {{ $req['status'] }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>

</div>

<script>
    function updateTime() {
        const now = new Date();
        let hours = now.getHours();
        let minutes = now.getMinutes();
        let seconds = now.getSeconds();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        
        hours = hours % 12;
        hours = hours ? hours : 12; // the hour '0' should be '12'
        minutes = minutes < 10 ? '0'+minutes : minutes;
        seconds = seconds < 10 ? '0'+seconds : seconds;
        
        const strTime = hours + ':' + minutes + ':' + seconds + ' ' + ampm;
        document.getElementById('liveTime').textContent = strTime;
    }
    setInterval(updateTime, 1000);
    updateTime();
</script>
@endsection
