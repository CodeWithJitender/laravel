@extends('layouts.app')

@section('title', 'Attendance Correction Requests')
@section('page_title', 'Attendance Correction Requests')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Top Action Panel -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <p class="text-sm text-slate-400">Manage and review requests for manual adjustment of work timings.</p>
        </div>
        
        @can('attendance.correction.request')
            <a href="{{ route('attendance.corrections.create') }}" 
               class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-xs transition duration-200 cursor-pointer flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Correction Request
            </a>
        @endcan
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-2xl text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-2xl text-sm">
            {{ session('error') }}
        </div>
    @endif

    <!-- Table Card -->
    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl overflow-hidden shadow-2xl">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-white/5 bg-slate-900/30 text-slate-400 text-xs font-bold uppercase tracking-wider">
                    <th class="px-6 py-4">Employee</th>
                    <th class="px-6 py-4">Requested Date</th>
                    <th class="px-6 py-4">Requested Clock In</th>
                    <th class="px-6 py-4">Requested Clock Out</th>
                    <th class="px-6 py-4">Reason</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5 text-sm text-slate-200">
                @forelse($corrections as $cor)
                    <tr class="hover:bg-white/2 transition duration-150">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-800 text-indigo-400 border border-slate-700 flex items-center justify-center font-bold text-xs">
                                    {{ substr($cor->user->name, 0, 2) }}
                                </div>
                                <div>
                                    <span class="block font-semibold">{{ $cor->user->name }}</span>
                                    <span class="block text-[10px] text-slate-400 font-mono">{{ $cor->user->employeeDetail?->employee_code ?? '-' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 font-semibold">
                            {{ $cor->requested_date->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 font-mono text-xs text-slate-300">
                            {{ $cor->requested_clock_in ? $cor->requested_clock_in->format('h:i A') : '-' }}
                        </td>
                        <td class="px-6 py-4 font-mono text-xs text-slate-300">
                            {{ $cor->requested_clock_out ? $cor->requested_clock_out->format('h:i A') : '-' }}
                        </td>
                        <td class="px-6 py-4 max-w-xs truncate text-slate-300" title="{{ $cor->reason }}">
                            {{ $cor->reason }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg uppercase tracking-wider border 
                                @if($cor->status == 'approved') bg-emerald-500/10 text-emerald-400 border-emerald-500/20
                                @elseif($cor->status == 'pending') bg-amber-500/10 text-amber-400 border-amber-500/20
                                @else bg-rose-500/10 text-rose-400 border-rose-500/20
                                @endif">
                                {{ $cor->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if(auth()->user()->hasRole('Admin') || auth()->user()->hasPermissionTo('attendance.correction.approve'))
                                @if($cor->status == 'pending')
                                    <a href="{{ route('attendance.corrections.show', $cor->id) }}" 
                                       class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold transition duration-200 inline-block cursor-pointer">
                                        Review
                                    </a>
                                @else
                                    <a href="{{ route('attendance.corrections.show', $cor->id) }}" 
                                       class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 border border-white/5 rounded-lg text-xs font-semibold transition duration-200 inline-block cursor-pointer">
                                        View details
                                    </a>
                                @endif
                            @else
                                <a href="{{ route('attendance.corrections.show', $cor->id) }}" 
                                   class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 border border-white/5 rounded-lg text-xs font-semibold transition duration-200 inline-block cursor-pointer">
                                    View
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-slate-500">
                            No correction requests found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $corrections->appends(request()->all())->links() }}
    </div>
</div>
@endsection
