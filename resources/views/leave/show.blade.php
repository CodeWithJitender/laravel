@extends('layouts.app')

@section('title', 'Leave Application Details')
@section('page_title', 'Leave Application Details')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Back Link & Action Bar -->
    <div class="flex items-center justify-between">
        <a href="{{ route('leave.index') }}" 
           class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-white/5 font-semibold rounded-xl text-xs transition duration-200 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Dashboard
        </a>

        @if($leaveRequest->status === 'pending' && $leaveRequest->employee_id === auth()->id())
            <form action="{{ route('leave.cancel', $leaveRequest->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to withdraw this leave request?');">
                @csrf
                @method('PUT')
                <button type="submit" class="px-4 py-2 bg-rose-600/10 hover:bg-rose-600/20 text-rose-400 border border-rose-500/20 font-semibold rounded-xl text-xs transition duration-200 cursor-pointer">
                    Withdraw Leave Request
                </button>
            </form>
        @endif
    </div>

    <!-- Alert Messages -->
    @if(session('error'))
        <div class="p-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-2xl text-sm shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Details Column -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Details Panel -->
            <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-6 shadow-2xl space-y-6">
                <!-- Header -->
                <div class="flex items-center gap-4 border-b border-white/5 pb-6">
                    <div class="w-14 h-14 rounded-2xl bg-slate-800 text-indigo-400 border border-slate-700 flex items-center justify-center font-bold text-lg shadow-lg">
                        {{ substr($leaveRequest->employee->name, 0, 2) }}
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-100">{{ $leaveRequest->employee->name }}</h2>
                        <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $leaveRequest->employee->employeeDetail?->employee_code ?? '-' }} &bull; {{ $leaveRequest->employee->employeeDetail?->department?->department_name ?? '-' }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6 text-sm">
                    <div>
                        <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Leave Category</span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 text-xs rounded-lg font-semibold border"
                              style="background-color: {{ $leaveRequest->leaveType->color }}10; color: {{ $leaveRequest->leaveType->color }}; border-color: {{ $leaveRequest->leaveType->color }}20;">
                            {{ $leaveRequest->leaveType->name }}
                        </span>
                    </div>

                    <div>
                        <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Status</span>
                        <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-lg uppercase tracking-wider border 
                            @if($leaveRequest->status == 'approved') bg-emerald-500/10 text-emerald-400 border-emerald-500/20
                            @elseif($leaveRequest->status == 'pending') bg-amber-500/10 text-amber-400 border-amber-500/20
                            @elseif($leaveRequest->status == 'rejected') bg-rose-500/10 text-rose-400 border-rose-500/20
                            @else bg-slate-500/10 text-slate-400 border-slate-500/20
                            @endif">
                            {{ $leaveRequest->status }}
                        </span>
                    </div>

                    <div>
                        <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Requested Dates</span>
                        <span class="text-slate-200 font-mono font-semibold">{{ $leaveRequest->start_date->format('M d, Y') }} &bull; {{ $leaveRequest->end_date->format('M d, Y') }}</span>
                    </div>

                    <div>
                        <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Total Weight Duration</span>
                        <span class="text-indigo-400 font-bold font-mono">{{ number_format($leaveRequest->total_days, 1) }} days</span>
                    </div>

                    <div>
                        <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Emergency Contact</span>
                        <span class="text-slate-200 font-mono">{{ $leaveRequest->emergency_phone }}</span>
                    </div>

                    <div>
                        <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Applied At</span>
                        <span class="text-slate-400 font-mono text-xs">{{ $leaveRequest->applied_at->format('M d, Y h:i A') }}</span>
                    </div>

                    <div class="col-span-2">
                        <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Reason for Leave</span>
                        <p class="text-slate-300 mt-1 leading-relaxed bg-slate-950/40 p-4 rounded-2xl border border-white/5 whitespace-pre-line">{{ $leaveRequest->reason }}</p>
                    </div>

                    @if($leaveRequest->attachment_path)
                        <div class="col-span-2">
                            <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Supporting Documents</span>
                            <a href="{{ Storage::url($leaveRequest->attachment_path) }}" target="_blank" 
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

            <!-- Manager Review Actions -->
            @if($leaveRequest->status === 'pending' && (auth()->user()->hasRole('Admin') || auth()->user()->hasPermissionTo('leave.approve')) && $leaveRequest->employee_id !== auth()->id())
                <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-6 shadow-2xl space-y-4">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-white/5 pb-2">Manager Review Decisions</h3>
                    
                    <form action="{{ route('leave.review', $leaveRequest->id) }}" method="POST" id="review-form" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" id="review-status" value="">

                        <div>
                            <label for="remarks" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Remarks / Rejection Reason</label>
                            <textarea name="remarks" id="remarks" rows="3" placeholder="Provide comments or explaining rejection details (required if rejecting)..."
                                      class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-2.5 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm @error('remarks') border-rose-500 focus:ring-rose-500 @enderror"></textarea>
                            @error('remarks')
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
                        const comment = document.getElementById('remarks').value.trim();

                        if (status === 'rejected' && comment.length < 5) {
                            alert('Please provide a rejection reason of at least 5 characters.');
                            return;
                        }

                        statusInput.value = status;
                        form.submit();
                    }
                </script>
            @endif

            <!-- History Logs Timeline -->
            <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-6 shadow-2xl">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 border-b border-white/5 pb-2">Status Audit History</h3>
                <div class="space-y-4 font-sans text-sm">
                    @forelse($leaveRequest->statusHistory as $hist)
                        <div class="flex items-start gap-4">
                            <div class="w-2 h-2 rounded-full bg-indigo-500 mt-1.5 shrink-0"></div>
                            <div>
                                <span class="block font-semibold text-slate-200 uppercase text-xs">{{ $hist->status }}</span>
                                <span class="block text-[10px] text-slate-400 font-mono mt-0.5">By: {{ $hist->user->name }} &bull; {{ $hist->created_at->format('M d, Y h:i A') }}</span>
                                @if($hist->remarks)
                                    <span class="block text-xs text-slate-400 italic mt-1">"{{ $hist->remarks }}"</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-slate-500 text-center py-4">No audit histories found.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar Balances Column -->
        <div class="space-y-6">
            <!-- Balance Info -->
            <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-6 shadow-2xl">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 border-b border-white/5 pb-2">Employee Balances</h3>
                <div class="space-y-4">
                    @forelse($balances as $bal)
                        <div class="flex justify-between items-center text-sm border-b border-white/5 pb-2">
                            <span class="text-slate-400 truncate max-w-[120px]" title="{{ $bal->leaveType->name }}">{{ $bal->leaveType->name }}</span>
                            <span class="font-bold font-mono text-indigo-400">{{ number_format($bal->remaining_balance, 1) }} days</span>
                        </div>
                    @empty
                        <div class="text-center py-4 text-slate-500 text-xs">No active leave balances assigned.</div>
                    @endforelse
                </div>
            </div>

            <!-- Guidelines Warning Box -->
            <div class="backdrop-blur-md bg-white/5 border border-indigo-500/10 rounded-3xl p-6 shadow-2xl">
                <h4 class="text-xs font-bold text-indigo-400 uppercase tracking-wider mb-2 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Workflow Notes
                </h4>
                <ul class="text-[11px] text-slate-400 space-y-2 list-disc list-inside">
                    <li>Manager approval finalized leave balance deduction and updates attendance logs instantly.</li>
                    <li>Rejections release the employee's pending balance limit immediately.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
