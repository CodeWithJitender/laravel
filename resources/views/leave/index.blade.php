@extends('layouts.app')

@section('title', 'Leave Dashboard')
@section('page_title', 'Leave Management')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Top Info Bar -->
    <div class="flex justify-between items-center">
        <div>
            <p class="text-sm text-slate-400">View your current leave balances, submit requests, and track approval statuses.</p>
        </div>
        
        @can('leave.create')
            <a href="{{ route('leave.create') }}" 
               class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-xs transition duration-200 cursor-pointer flex items-center gap-2 shadow-lg shadow-indigo-500/10">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Apply for Leave
            </a>
        @endcan
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-2xl text-sm shadow-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-2xl text-sm shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    <!-- Tab Navigation -->
    <div class="flex items-center gap-2 border-b border-white/10 pb-4">
        <a href="{{ route('leave.index', ['tab' => 'balance']) }}" 
           class="px-4 py-2.5 text-xs font-semibold rounded-xl border border-white/5 transition duration-150 {{ request()->get('tab') === 'balance' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/15 border-indigo-500/10' : 'bg-slate-900/50 text-slate-400 hover:text-slate-200' }}">
            Leave Balance Sheet
        </a>
        <a href="{{ route('leave.index', ['tab' => 'history']) }}" 
           class="px-4 py-2.5 text-xs font-semibold rounded-xl border border-white/5 transition duration-150 {{ request()->get('tab') === 'history' || !request()->has('tab') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/15 border-indigo-500/10' : 'bg-slate-900/50 text-slate-400 hover:text-slate-200' }}">
            Applications History
        </a>
        <a href="{{ route('leave.index', ['tab' => 'status']) }}" 
           class="px-4 py-2.5 text-xs font-semibold rounded-xl border border-white/5 transition duration-150 {{ request()->get('tab') === 'status' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/15 border-indigo-500/10' : 'bg-slate-900/50 text-slate-400 hover:text-slate-200' }}">
            Approval Statuses
        </a>
    </div>

    @php
        $currentTab = request()->get('tab', 'history');
        if ($currentTab === 'status') {
            // Filter pending requests for this view
            $filteredRequests = $requests->filter(fn($r) => $r->status === 'pending');
        } else {
            $filteredRequests = $requests;
        }
    @endphp

    @if($currentTab === 'balance')
        <!-- Balances Grid -->
        <div>
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">My Leave Balance Sheets</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @forelse($balances as $bal)
                    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-5 shadow-xl relative overflow-hidden group hover:border-white/20 transition duration-200">
                        <div class="absolute top-[-30px] right-[-30px] w-24 h-24 rounded-full opacity-10 group-hover:scale-110 transition duration-300" style="background-color: {{ $bal->leaveType->color }};"></div>
                        
                        <span class="block text-xs font-bold text-slate-400 uppercase tracking-wide truncate">{{ $bal->leaveType->name }}</span>
                        <span class="block text-3xl font-bold font-mono mt-2" style="color: {{ $bal->leaveType->color }};">
                            {{ number_format($bal->remaining_balance, 1) }} <span class="text-xs text-slate-400">days</span>
                        </span>

                        <div class="grid grid-cols-2 gap-2 mt-4 pt-3 border-t border-white/5 text-[10px] text-slate-400">
                            <div>Allocated: <span class="font-mono text-slate-200 font-semibold">{{ number_format($bal->allocated_balance, 1) }}</span></div>
                            <div>Accrued: <span class="font-mono text-slate-200 font-semibold">{{ number_format($bal->accrued_balance, 1) }}</span></div>
                            <div>Used: <span class="font-mono text-rose-400 font-semibold">{{ number_format($bal->used_balance, 1) }}</span></div>
                            <div>CF: <span class="font-mono text-indigo-400 font-semibold">{{ number_format($bal->carry_forward_balance, 1) }}</span></div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-6 text-center text-slate-500 text-sm">
                        No active leave balances assigned. Accrual engines or admin allocations will generate records here.
                    </div>
                @endforelse
            </div>
        </div>
    @else
        <!-- Requests Table List -->
        <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl overflow-hidden shadow-2xl">
            <div class="p-6 border-b border-white/5 bg-slate-900/30 flex justify-between items-center">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                    @if($currentTab === 'status')
                        Pending Applications Status
                    @else
                        Leave Applications Log
                    @endif
                </h3>
            </div>

            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-white/5 bg-slate-900/10 text-slate-400 text-xs font-bold uppercase tracking-wider">
                        <th class="px-6 py-4">Employee</th>
                        <th class="px-6 py-4">Leave Type</th>
                        <th class="px-6 py-4">Duration</th>
                        <th class="px-6 py-4 text-center">Days</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-sm text-slate-200">
                    @forelse($filteredRequests as $req)
                        <tr class="hover:bg-white/2 transition duration-150">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-800 text-indigo-400 border border-slate-700 flex items-center justify-center font-bold text-xs">
                                        {{ substr($req->employee->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <span class="block font-semibold">{{ $req->employee->name }}</span>
                                        <span class="block text-[10px] text-slate-400 font-mono">{{ $req->employee->employeeDetail?->employee_code ?? '-' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 text-xs rounded-lg font-semibold border"
                                      style="background-color: {{ $req->leaveType->color }}10; color: {{ $req->leaveType->color }}; border-color: {{ $req->leaveType->color }}20;">
                                    {{ $req->leaveType->name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-slate-300">
                                {{ $req->start_date->format('M d, Y') }} &rarr; {{ $req->end_date->format('M d, Y') }}
                                @if($req->half_day)
                                    <span class="block text-[10px] text-amber-400 mt-0.5">Half Day ({{ strtoupper($req->half_day_session) }})</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center font-bold font-mono text-indigo-400">
                                {{ number_format($req->total_days, 1) }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg uppercase tracking-wider border 
                                    @if($req->status == 'approved') bg-emerald-500/10 text-emerald-400 border-emerald-500/20
                                    @elseif($req->status == 'pending') bg-amber-500/10 text-amber-400 border-amber-500/20
                                    @elseif($req->status == 'rejected') bg-rose-500/10 text-rose-400 border-rose-500/20
                                    @else bg-slate-500/10 text-slate-400 border-slate-500/20
                                    @endif">
                                    {{ $req->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('leave.show', $req->id) }}" 
                                   class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg text-xs font-semibold border border-white/5 transition duration-200 inline-block cursor-pointer">
                                    @if($req->status == 'pending' && (auth()->user()->hasRole('Admin') || auth()->user()->hasPermissionTo('leave.approve')) && $req->employee_id !== auth()->id())
                                        Review
                                    @else
                                        Details
                                    @endif
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-500">
                                No leave applications found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages() && $currentTab !== 'status')
            <div class="mt-6">
                {{ $requests->appends(request()->all())->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
