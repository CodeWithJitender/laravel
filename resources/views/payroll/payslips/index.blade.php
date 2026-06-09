@extends('layouts.app')

@section('title', 'My Payslips')
@section('page_title', 'Salary Payslips Archive')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center bg-white/5 border border-white/10 rounded-3xl p-6 backdrop-blur-md">
        <div>
            <h2 class="text-xl font-bold text-slate-200">
                @can('payroll.view')
                    All Employee Payslips
                @else
                    My Payslips Archive
                @endcan
            </h2>
            <p class="text-sm text-slate-400">View and download payroll payslips in corporate print-friendly format.</p>
        </div>
        @can('payroll.view')
        <a href="{{ route('payroll.index') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-semibold border border-white/5 transition duration-200">
            Back to Runs
        </a>
        @endcan
    </div>

    <!-- Payslips Grid -->
    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-8 shadow-2xl relative overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-white/5 text-slate-400 text-xs uppercase tracking-wider font-semibold">
                        <th class="py-4 px-6">Reference No</th>
                        @can('payroll.view')
                            <th class="py-4 px-6">Employee</th>
                        @endcan
                        <th class="py-4 px-6">Earnings</th>
                        <th class="py-4 px-6">Deductions</th>
                        <th class="py-4 px-6">Net Salary Credited</th>
                        <th class="py-4 px-6">Generated Date</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-slate-300 text-sm">
                    @forelse($payslips as $slip)
                        <tr class="hover:bg-white/5 transition duration-150">
                            <td class="py-4 px-6 font-semibold text-slate-200">
                                {{ $slip->reference_no }}
                            </td>
                            @can('payroll.view')
                                <td class="py-4 px-6 text-slate-300">
                                    {{ $slip->employee?->name }}
                                </td>
                            @endcan
                            <td class="py-4 px-6 font-mono text-xs">
                                ₹{{ number_format($slip->total_earnings, 2) }}
                            </td>
                            <td class="py-4 px-6 font-mono text-xs text-rose-400">
                                ₹{{ number_format($slip->total_deductions, 2) }}
                            </td>
                            <td class="py-4 px-6 font-mono text-emerald-400 font-bold">
                                ₹{{ number_format($slip->net_salary, 2) }}
                            </td>
                            <td class="py-4 px-6 text-xs text-slate-400">
                                {{ $slip->generated_at ? $slip->generated_at->format('M d, Y') : 'N/A' }}
                            </td>
                            <td class="py-4 px-6 text-right flex justify-end gap-2">
                                <a href="{{ route('payslips.show', $slip->id) }}" class="px-3 py-1.5 bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-400 border border-indigo-500/20 rounded-lg text-xs font-semibold transition">
                                    Show Detail
                                </a>
                                <a href="{{ route('payslips.download', $slip->id) }}" target="_blank" class="px-3 py-1.5 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/20 rounded-lg text-xs font-semibold transition">
                                    Print / PDF
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-500">
                                No published payslips found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">
            {{ $payslips->links() }}
        </div>
    </div>
</div>
@endsection
