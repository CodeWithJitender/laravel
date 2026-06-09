@extends('layouts.app')

@section('title', 'Payroll Runs')
@section('page_title', 'Payroll Management')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header Controls -->
    <div class="flex justify-between items-center bg-white/5 border border-white/10 rounded-3xl p-6 backdrop-blur-md">
        <div>
            <h2 class="text-xl font-bold text-slate-200">Payroll Cycle Runs</h2>
            <p class="text-sm text-slate-400">View and execute monthly payroll runs, adjustments, and approvals.</p>
        </div>
        <div class="flex gap-3">
            @can('payroll.structure.manage')
            <a href="{{ route('salary-structures.index') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-semibold border border-white/5 transition duration-200">
                Manage Structures
            </a>
            @endcan
            @can('payroll.revision.manage')
            <a href="{{ route('salary-revisions.index') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-semibold border border-white/5 transition duration-200">
                Salary Revisions
            </a>
            @endcan
            @can('payroll.process')
            <a href="{{ route('payroll.create') }}" class="px-5 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-semibold rounded-xl text-xs shadow-lg shadow-indigo-500/25 transition duration-200">
                Start New Payroll Run
            </a>
            @endcan
        </div>
    </div>

    <!-- Sessions / Runs Grid -->
    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-8 shadow-2xl relative overflow-hidden">
        <h3 class="text-lg font-semibold text-slate-200 mb-6">Recent Runs</h3>

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

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-white/5 text-slate-400 text-xs uppercase tracking-wider font-semibold">
                        <th class="py-4 px-6">Period</th>
                        <th class="py-4 px-6">Type</th>
                        <th class="py-4 px-6">Total Employees</th>
                        <th class="py-4 px-6">Total Net Payout</th>
                        <th class="py-4 px-6">Status</th>
                        <th class="py-4 px-6">Processed Date</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-slate-300 text-sm">
                    @forelse($runs as $run)
                        <tr class="hover:bg-white/5 transition duration-150">
                            <td class="py-4 px-6 font-semibold text-slate-200">
                                {{ Carbon\Carbon::createFromDate($run->run_year, $run->run_month, 1)->format('F Y') }}
                            </td>
                            <td class="py-4 px-6 capitalize">
                                {{ str_replace('_', ' ', $run->run_type) }}
                            </td>
                            <td class="py-4 px-6">
                                {{ $run->total_employees }}
                            </td>
                            <td class="py-4 px-6 font-mono text-emerald-400">
                                ₹{{ number_format($run->total_net, 2) }}
                            </td>
                            <td class="py-4 px-6">
                                @if($run->status === 'published')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Published</span>
                                @elseif($run->status === 'approved')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">Approved</span>
                                @elseif($run->status === 'calculated')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">Calculated</span>
                                @elseif($run->status === 'processing')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20 animate-pulse">Processing</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-500/10 text-slate-400 border border-slate-500/20">Draft</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-slate-400 text-xs">
                                {{ $run->processed_at ? $run->processed_at->format('M d, Y h:i A') : 'N/A' }}
                            </td>
                            <td class="py-4 px-6 text-right">
                                <a href="{{ route('payroll.show', $run->id) }}" class="px-3 py-1.5 bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-400 border border-indigo-500/20 rounded-lg text-xs font-semibold transition duration-150">
                                    View Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-500">
                                No payroll cycle runs found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $runs->links() }}
        </div>
    </div>
</div>
@endsection
