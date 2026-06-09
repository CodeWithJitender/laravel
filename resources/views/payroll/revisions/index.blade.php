@extends('layouts.app')

@section('title', 'Salary Revisions')
@section('page_title', 'Employee Salary Revisions')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header with Actions -->
    <div class="flex justify-between items-center bg-white/5 border border-white/10 rounded-3xl p-6 backdrop-blur-md">
        <div>
            <h2 class="text-xl font-bold text-slate-200">CTC & Salary Revisions</h2>
            <p class="text-sm text-slate-400">Log history of CTC increments, hikes, and structure changes.</p>
        </div>
        <a href="{{ route('payroll.index') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-semibold border border-white/5 transition duration-200">
            Back to Runs
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Propose Revision Form -->
        <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-6 shadow-2xl space-y-4">
            <h3 class="text-md font-semibold text-slate-200 border-b border-white/5 pb-2">Propose CTC Revision</h3>
            <form action="{{ route('salary-revisions.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="employee_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Select Employee</label>
                    <select name="employee_id" id="employee_id" required class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm">
                        <option value="">-- Choose Employee --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="new_gross_salary" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">New Monthly Gross (₹)</label>
                    <input type="number" step="0.01" name="new_gross_salary" id="new_gross_salary" required class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm" placeholder="e.g. 80000.00">
                </div>
                <div>
                    <label for="effective_date" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Effective Date</label>
                    <input type="date" name="effective_date" id="effective_date" required class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm" value="{{ date('Y-m-d') }}">
                </div>
                <div>
                    <label for="reason" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Reason</label>
                    <textarea name="reason" id="reason" rows="2" class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm" placeholder="e.g. Annual Appraisal / promotion..."></textarea>
                </div>
                <button type="submit" class="w-full py-3 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-semibold rounded-2xl text-xs shadow-lg shadow-indigo-500/25 transition">
                    Submit Proposal
                </button>
            </form>
        </div>

        <!-- Revisions History list -->
        <div class="lg:col-span-2 backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-8 shadow-2xl relative overflow-hidden">
            <h3 class="text-lg font-semibold text-slate-200 mb-6 font-bold">Revision Logs</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-white/5 text-slate-400 text-xs uppercase tracking-wider font-semibold">
                            <th class="py-4 px-6">Employee</th>
                            <th class="py-4 px-6 text-right">Old Gross</th>
                            <th class="py-4 px-6 text-right">New Gross</th>
                            <th class="py-4 px-6">Effective Date</th>
                            <th class="py-4 px-6">Status / Approver</th>
                            <th class="py-4 px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-slate-300 text-sm">
                        @forelse($revisions as $rev)
                            <tr class="hover:bg-white/5 transition duration-150">
                                <td class="py-4 px-6">
                                    <div class="font-semibold text-slate-200">{{ $rev->employee->name }}</div>
                                    <div class="text-xs text-slate-400">{{ $rev->reason ?? 'No reason' }}</div>
                                </td>
                                <td class="py-4 px-6 text-right font-mono text-slate-400">
                                    ₹{{ number_format($rev->old_gross_salary, 2) }}
                                </td>
                                <td class="py-4 px-6 text-right font-mono text-emerald-400">
                                    ₹{{ number_format($rev->new_gross_salary, 2) }}
                                </td>
                                <td class="py-4 px-6">
                                    {{ $rev->effective_date->format('M d, Y') }}
                                </td>
                                <td class="py-4 px-6">
                                    @if($rev->approved_by)
                                        <span class="text-emerald-400 text-xs font-semibold">Approved by {{ $rev->approver?->name }}</span>
                                    @else
                                        <span class="text-amber-400 text-xs font-semibold animate-pulse">Pending Review</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-right">
                                    @if(!$rev->approved_by)
                                        <form action="{{ route('salary-revisions.approve', $rev->id) }}" method="POST" class="inline">
                                            @csrf
                                            <!-- Optionally specify salary structure -->
                                            <input type="hidden" name="salary_structure_id" value="{{ $structures->first()?->id }}">
                                            <button type="submit" class="px-3 py-1 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/20 rounded-lg text-xs font-semibold transition">
                                                Approve
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-slate-500 text-xs">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-500">
                                    No salary revisions found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-6">
                {{ $revisions->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
