@extends('layouts.app')

@section('title', 'Timecard Punch')
@section('page_title', 'Digital Timecard Console')

@section('content')
<div class="max-w-2xl mx-auto">

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <!-- Timecard Card -->
    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-8 shadow-2xl relative overflow-hidden text-center">
        <div class="absolute top-[-20%] left-[-10%] w-[50%] h-[50%] rounded-full bg-indigo-500/5 blur-[80px] pointer-events-none"></div>

        <p class="text-xs font-bold text-indigo-400 uppercase tracking-widest mb-2">Shift Schedule: {{ $shift ? $shift->shift_name . ' (' . date('h:i A', strtotime($shift->start_time)) . ' - ' . date('h:i A', strtotime($shift->end_time)) . ')' : 'None assigned' }}</p>

        <!-- Live Clock -->
        <div class="py-6">
            <span id="live-clock" class="text-5xl font-mono font-bold tracking-tight text-white drop-shadow-[0_4px_12px_rgba(99,102,241,0.2)]">00:00:00 AM</span>
            <span id="live-date" class="block text-sm text-slate-400 font-medium mt-2">Thursday, June 4, 2026</span>
        </div>

        <div class="my-6 max-w-sm mx-auto border-t border-white/5 pt-6">
            @if($activeSession)
                <!-- Clock Out Form -->
                <form action="{{ route('attendance.clock_out') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <span class="block text-xs text-slate-500 mb-1">Clocked In at: <span class="font-mono text-slate-300 font-bold">{{ $activeSession->clock_in->format('h:i:s A') }}</span></span>
                        <span class="block text-[10px] text-slate-500">IP logged: {{ $activeSession->logs()->where('type', 'clock_in')->first()?->ip_address ?? '-' }}</span>
                    </div>

                    <div>
                        <label for="remarks" class="block text-left text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Punch Out Remarks (Optional)</label>
                        <input type="text" name="remarks" id="remarks"
                            class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm text-center" 
                            placeholder="e.g. Completed today's targets">
                    </div>

                    <button type="submit"
                        class="w-full py-4 bg-gradient-to-r from-rose-500 to-orange-600 hover:from-rose-600 hover:to-orange-700 text-white font-bold rounded-2xl text-sm shadow-xl shadow-rose-500/25 transition duration-200 transform hover:-translate-y-0.5 cursor-pointer">
                        Clock Out (End Work)
                    </button>
                </form>
            @elseif($todaySession && $todaySession->clock_out)
                <!-- Complete Badge -->
                <div class="p-6 bg-slate-900/40 rounded-2xl border border-white/5">
                    <span class="px-3 py-1 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-xs font-bold rounded-lg uppercase tracking-wide">
                        Day Complete
                    </span>
                    <p class="text-xs text-slate-400 mt-3">
                        You have finished work for today.<br>
                        Clock In: <span class="font-mono text-slate-300">{{ $todaySession->clock_in->format('h:i A') }}</span> | 
                        Clock Out: <span class="font-mono text-slate-300">{{ $todaySession->clock_out->format('h:i A') }}</span>
                    </p>
                    <p class="text-xs font-bold text-indigo-400 mt-2 font-mono">
                        Worked Hours: {{ $todaySession->worked_hours }} hrs
                    </p>
                </div>
            @else
                <!-- Clock In Form -->
                <form action="{{ route('attendance.clock_in') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="remarks" class="block text-left text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Punch In Remarks (Optional)</label>
                        <input type="text" name="remarks" id="remarks"
                            class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm text-center" 
                            placeholder="e.g. Work starting on client project">
                    </div>

                    <button type="submit"
                        class="w-full py-4 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold rounded-2xl text-sm shadow-xl shadow-emerald-500/25 transition duration-200 transform hover:-translate-y-0.5 cursor-pointer">
                        Clock In (Start Work)
                    </button>
                </form>
            @endif
        </div>
    </div>

</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const timeEl = document.getElementById("live-clock");
        const dateEl = document.getElementById("live-date");

        function updateClock() {
            const now = new Date();
            let hours = now.getHours();
            let minutes = now.getMinutes();
            let seconds = now.getSeconds();
            const ampm = hours >= 12 ? 'PM' : 'AM';
            
            hours = hours % 12;
            hours = hours ? hours : 12; // 0 should be 12
            hours = hours < 10 ? '0' + hours : hours;
            minutes = minutes < 10 ? '0' + minutes : minutes;
            seconds = seconds < 10 ? '0' + seconds : seconds;
            
            timeEl.textContent = `${hours}:${minutes}:${seconds} ${ampm}`;

            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            dateEl.textContent = now.toLocaleDateString('en-US', options);
        }

        updateClock();
        setInterval(updateClock, 1000);
    });
</script>
@endsection
