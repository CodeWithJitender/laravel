@extends('layouts.app')

@section('title', 'Correction Request Details')
@section('page_title', 'Correction Request Details')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('attendance.corrections.index') }}" 
           class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-white/5 font-semibold rounded-xl text-xs transition duration-200 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to List
        </a>
    </div>

    <!-- Alert Messages -->
    @if(session('error'))
        <div class="mb-6 p-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-2xl text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Main Details Column -->
        <div class="md:col-span-2 space-y-6">
            <!-- Requester Panel -->
            <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-6 shadow-2xl">
                <div class="flex items-center gap-4 mb-6 border-b border-white/5 pb-6">
                    <div class="w-14 h-14 rounded-2xl bg-slate-800 text-indigo-400 border border-slate-700 flex items-center justify-center font-bold text-lg shadow-lg">
                        {{ substr($correction->user->name, 0, 2) }}
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-100">{{ $correction->user->name }}</h2>
                        <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $correction->user->employeeDetail?->employee_code ?? '-' }} &bull; {{ $correction->user->employeeDetail?->department?->department_name ?? '-' }}</p>
                    </div>
                </div>

                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Request Details</h3>
                <div class="grid grid-cols-2 gap-6 text-sm">
                    <div>
                        <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Target Date</span>
                        <span class="text-slate-200 font-semibold">{{ $correction->requested_date->format('F d, Y') }}</span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Status</span>
                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-lg uppercase tracking-wider border 
                            @if($correction->status == 'approved') bg-emerald-500/10 text-emerald-400 border-emerald-500/20
                            @elseif($correction->status == 'pending') bg-amber-500/10 text-amber-400 border-amber-500/20
                            @else bg-rose-500/10 text-rose-400 border-rose-500/20
                            @endif">
                            {{ $correction->status }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Requested Clock In</span>
                        <span class="text-slate-200 font-mono font-semibold">{{ $correction->requested_clock_in ? $correction->requested_clock_in->format('h:i A') : '-' }}</span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Requested Clock Out</span>
                        <span class="text-slate-200 font-mono font-semibold">{{ $correction->requested_clock_out ? $correction->requested_clock_out->format('h:i A') : '-' }}</span>
                    </div>
                    <div class="col-span-2">
                        <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Reason for Request</span>
                        <p class="text-slate-300 mt-1 leading-relaxed bg-slate-950/40 p-4 rounded-2xl border border-white/5 whitespace-pre-line">{{ $correction->reason }}</p>
                    </div>

                    @if($correction->attachment_path)
                        <div class="col-span-2">
                            <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Supporting Document</span>
                            <a href="{{ Storage::url($correction->attachment_path) }}" target="_blank" 
                               class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 border border-white/5 font-semibold rounded-xl text-xs transition duration-200 cursor-pointer">
                                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                View Attachment Document
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Reviewer Action Box -->
            @if($correction->status === 'pending' && (auth()->user()->hasRole('Admin') || auth()->user()->hasPermissionTo('attendance.correction.approve')))
                <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-6 shadow-2xl">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 border-b border-white/5 pb-2">Manager Review & Actions</h3>
                    
                    <form action="{{ route('attendance.corrections.review', $correction->id) }}" method="POST" id="review-form" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" id="review-status" value="">

                        <div>
                            <label for="rejection_reason" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Comments / Rejection Reason</label>
                            <textarea name="rejection_reason" id="rejection_reason" rows="3" placeholder="Provide a comment or explain reason for rejection (required only if rejecting)..."
                                      class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-2.5 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm @error('rejection_reason') border-rose-500 focus:ring-rose-500 @enderror"></textarea>
                            @error('rejection_reason')
                                <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" onclick="submitReview('rejected')" 
                                    class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-xl text-xs transition duration-200 cursor-pointer">
                                Reject Request
                            </button>
                            <button type="button" onclick="submitReview('approved')" 
                                    class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-xs transition duration-200 cursor-pointer">
                                Approve Request
                            </button>
                        </div>
                    </form>
                </div>

                <script>
                    function submitReview(status) {
                        const statusInput = document.getElementById('review-status');
                        const form = document.getElementById('review-form');
                        const comment = document.getElementById('rejection_reason').value.trim();

                        if (status === 'rejected' && comment.length < 5) {
                            alert('Please provide a rejection reason of at least 5 characters.');
                            return;
                        }

                        statusInput.value = status;
                        form.submit();
                    }
                </script>
            @endif

            <!-- Resolution Info Panel -->
            @if($correction->status !== 'pending')
                <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-6 shadow-2xl">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 border-b border-white/5 pb-2">Resolution Log</h3>
                    <div class="space-y-4 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Reviewed By:</span>
                            <span class="text-slate-200 font-semibold">{{ $correction->approvedBy?->name ?? 'System' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Date Resolved:</span>
                            <span class="text-slate-200 font-mono">{{ $correction->approved_at ? $correction->approved_at->format('M d, Y h:i A') : $correction->updated_at->format('M d, Y h:i A') }}</span>
                        </div>
                        @if($correction->status === 'rejected')
                            <div class="pt-2">
                                <span class="block text-[10px] font-bold text-rose-400 uppercase tracking-wider mb-1">Rejection Reason</span>
                                <p class="text-slate-300 italic">"{{ $correction->rejection_reason }}"</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar Comparison Column -->
        <div class="space-y-6">
            <!-- Existing Reference Box -->
            <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-6 shadow-2xl">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 border-b border-white/5 pb-2 font-mono">Original Record</h3>
                
                @if($correction->attendance)
                    <div class="space-y-4 text-sm">
                        <div>
                            <span class="block text-[10px] text-slate-500 uppercase tracking-wider mb-0.5">Original Clock In</span>
                            <span class="font-mono text-slate-200 font-semibold">{{ $correction->attendance->clock_in ? $correction->attendance->clock_in->format('h:i A') : 'No Clock In' }}</span>
                        </div>
                        <div>
                            <span class="block text-[10px] text-slate-500 uppercase tracking-wider mb-0.5">Original Clock Out</span>
                            <span class="font-mono text-slate-200 font-semibold">{{ $correction->attendance->clock_out ? $correction->attendance->clock_out->format('h:i A') : 'No Clock Out' }}</span>
                        </div>
                        <div>
                            <span class="block text-[10px] text-slate-500 uppercase tracking-wider mb-0.5">Worked Duration</span>
                            <span class="text-indigo-400 font-semibold font-mono">{{ $correction->attendance->worked_hours ?? '0.00' }} hrs</span>
                        </div>
                        <div>
                            <span class="block text-[10px] text-slate-500 uppercase tracking-wider mb-0.5">Original Status</span>
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-lg uppercase tracking-wider border 
                                @if($correction->attendance->attendance_status == 'Present') bg-emerald-500/10 text-emerald-400 border-emerald-500/20
                                @elseif($correction->attendance->attendance_status == 'Late') bg-amber-500/10 text-amber-400 border-amber-500/20
                                @elseif($correction->attendance->attendance_status == 'Half Day') bg-blue-500/10 text-blue-400 border-blue-500/20
                                @elseif($correction->attendance->attendance_status == 'Work From Home') bg-purple-500/10 text-purple-400 border-purple-500/20
                                @elseif($correction->attendance->attendance_status == 'Missed Punch') bg-orange-500/10 text-orange-400 border-orange-500/20
                                @else bg-rose-500/10 text-rose-400 border-rose-500/20
                                @endif">
                                {{ $correction->attendance->attendance_status }}
                            </span>
                        </div>
                        <div class="pt-2 border-t border-white/5">
                            <a href="{{ route('attendance.show', $correction->attendance->id) }}" 
                               class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold flex items-center gap-1">
                                View Full Day Details &rarr;
                            </a>
                        </div>
                    </div>
                @else
                    <div class="text-center py-6 text-slate-500 text-xs">
                        No previous attendance record exists on the target date. This will generate a new present log.
                    </div>
                @endif
            </div>

            <!-- Policy Warning Box -->
            <div class="backdrop-blur-md bg-white/5 border border-indigo-500/10 rounded-3xl p-6 shadow-2xl">
                <h4 class="text-xs font-bold text-indigo-400 uppercase tracking-wider mb-2 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Correction Rules
                </h4>
                <ul class="text-[11px] text-slate-400 space-y-2 list-disc list-inside">
                    <li>Manager approval updates the database punch record immediately.</li>
                    <li>Status values (Late, Under-hours, Overtime) are automatically re-calculated based on shift timings.</li>
                    <li>Activity logs are generated for auditing purposes.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
