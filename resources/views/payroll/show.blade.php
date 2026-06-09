@extends('layouts.app')

@section('title', 'Payroll Run Details')
@section('page_title', 'Payroll Run: ' . Carbon\Carbon::createFromDate($run->run_year, $run->run_month, 1)->format('F Y'))

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header with Breadcrumbs & Actions -->
    <div class="flex justify-between items-center bg-white/5 border border-white/10 rounded-3xl p-6 backdrop-blur-md">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('payroll.index') }}" class="hover:text-slate-200 transition">Runs</a>
                <span>&rarr;</span>
                <span class="text-slate-300">Run Details</span>
            </div>
            <h2 class="text-xl font-bold text-slate-200">
                {{ Carbon\Carbon::createFromDate($run->run_year, $run->run_month, 1)->format('F Y') }} Cycle Run
            </h2>
        </div>
        <div class="flex gap-3">
            @if(session('success'))
                <div class="self-center mr-4 text-emerald-400 text-sm font-semibold">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="self-center mr-4 text-rose-400 text-sm font-semibold">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Finance Approval Button -->
            @can('payroll.approve')
                @if($run->status === 'calculated' && !$run->approvals->where('approval_level', 'Finance')->where('status', 'approved')->first())
                    <form action="{{ route('payroll.approve', $run->id) }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="level" value="Finance">
                        <input type="hidden" name="remarks" value="Approved via system console">
                        <button type="submit" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow-lg shadow-blue-500/25 transition">
                            Finance Review & Approve
                        </button>
                    </form>
                @endif

                <!-- HR Approval Button -->
                @if($run->status === 'calculated' && $run->approvals->where('approval_level', 'Finance')->where('status', 'approved')->first() && !$run->approvals->where('approval_level', 'HR')->where('status', 'approved')->first())
                    <form action="{{ route('payroll.approve', $run->id) }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="level" value="HR">
                        <input type="hidden" name="remarks" value="Approved via system console">
                        <button type="submit" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-lg shadow-indigo-500/25 transition">
                            HR final Approve
                        </button>
                    </form>
                @endif
            @endcan

            <!-- Publish Button -->
            @can('payroll.publish')
                @if($run->status === 'approved')
                    <form action="{{ route('payroll.publish', $run->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-semibold rounded-xl text-xs shadow-lg shadow-emerald-500/25 transition">
                            Publish & Generate Payslips
                        </button>
                    </form>
                @endif
            @endcan
        </div>
    </div>

    <!-- Metadata Card / Summary KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white/5 border border-white/10 rounded-3xl p-6 backdrop-blur-md">
            <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Status</span>
            <div class="text-xl font-bold text-slate-200 capitalize">
                @if($run->status === 'published')
                    <span class="text-emerald-400">Published</span>
                @elseif($run->status === 'approved')
                    <span class="text-indigo-400">Approved</span>
                @elseif($run->status === 'calculated')
                    <span class="text-amber-400">Calculated</span>
                @elseif($run->status === 'processing')
                    <span class="text-blue-400 animate-pulse">Processing</span>
                @else
                    <span class="text-slate-400">Draft</span>
                @endif
            </div>
        </div>
        <div class="bg-white/5 border border-white/10 rounded-3xl p-6 backdrop-blur-md">
            <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Total Employees</span>
            <div class="text-2xl font-bold text-slate-200">{{ $run->total_employees }}</div>
        </div>
        <div class="bg-white/5 border border-white/10 rounded-3xl p-6 backdrop-blur-md">
            <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Total Earnings</span>
            <div class="text-2xl font-bold text-indigo-400">₹{{ number_format($run->total_earnings, 2) }}</div>
        </div>
        <div class="bg-white/5 border border-white/10 rounded-3xl p-6 backdrop-blur-md">
            <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Net Payout</span>
            <div class="text-2xl font-bold text-emerald-400">₹{{ number_format($run->total_net, 2) }}</div>
        </div>
    </div>

    <!-- Approval Workflow Track -->
    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-6 shadow-2xl relative overflow-hidden">
        <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-300 mb-4 pb-2 border-b border-white/5">Approval Pipeline Status</h3>
        <div class="flex flex-col md:flex-row md:items-center gap-6 md:gap-12">
            <!-- Process Step -->
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400 font-bold text-sm">✓</div>
                <div>
                    <span class="block text-xs font-semibold text-slate-200">1. Draft & Math Processing</span>
                    <span class="block text-xs text-slate-400">Processed by: {{ $run->processor?->name ?? 'System' }}</span>
                </div>
            </div>

            <div class="hidden md:block text-slate-600 font-bold text-lg">&rarr;</div>

            <!-- Finance Step -->
            <div class="flex items-center gap-3">
                @php $finApp = $run->approvals->where('approval_level', 'Finance')->where('status', 'approved')->first(); @endphp
                <div class="w-8 h-8 rounded-full {{ $finApp ? 'bg-emerald-500/10 border border-emerald-500/30 text-emerald-400' : 'bg-white/5 border border-white/10 text-slate-500' }} flex items-center justify-center font-bold text-sm">
                    {{ $finApp ? '✓' : '2' }}
                </div>
                <div>
                    <span class="block text-xs font-semibold {{ $finApp ? 'text-slate-200' : 'text-slate-400' }}">2. Finance Review</span>
                    <span class="block text-xs text-slate-400">{{ $finApp ? 'Approved by ' . $finApp->approver?->name : 'Pending Finance Approval' }}</span>
                </div>
            </div>

            <div class="hidden md:block text-slate-600 font-bold text-lg">&rarr;</div>

            <!-- HR Step -->
            <div class="flex items-center gap-3">
                @php $hrApp = $run->approvals->where('approval_level', 'HR')->where('status', 'approved')->first(); @endphp
                <div class="w-8 h-8 rounded-full {{ $hrApp ? 'bg-emerald-500/10 border border-emerald-500/30 text-emerald-400' : 'bg-white/5 border border-white/10 text-slate-500' }} flex items-center justify-center font-bold text-sm">
                    {{ $hrApp ? '✓' : '3' }}
                </div>
                <div>
                    <span class="block text-xs font-semibold {{ $hrApp ? 'text-slate-200' : 'text-slate-400' }}">3. HR final Approval</span>
                    <span class="block text-xs text-slate-400">{{ $hrApp ? 'Approved by ' . $hrApp->approver?->name : 'Pending HR Approval' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Employees List Grid -->
    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-8 shadow-2xl relative overflow-hidden">
        <h3 class="text-lg font-semibold text-slate-200 mb-6">Employee Breakdown</h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-white/5 text-slate-400 text-xs uppercase tracking-wider font-semibold">
                        <th class="py-4 px-6">Employee</th>
                        <th class="py-4 px-6">Department & Title</th>
                        <th class="py-4 px-6 text-center">Paid / LOP Days</th>
                        <th class="py-4 px-6">Gross Salary</th>
                        <th class="py-4 px-6">Earnings</th>
                        <th class="py-4 px-6">Deductions</th>
                        <th class="py-4 px-6">Net Pay</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-slate-300 text-sm">
                    @forelse($run->employees as $emp)
                        <tr class="hover:bg-white/5 transition duration-150">
                            <td class="py-4 px-6">
                                <div class="font-semibold text-slate-200">{{ $emp->employee->name }}</div>
                                <div class="text-xs text-slate-400">ID: EMP-{{ str_pad($emp->employee->id, 4, '0', STR_PAD_LEFT) }}</div>
                            </td>
                            <td class="py-4 px-6">
                                <div>{{ $emp->employee->employeeDetail?->designation?->designation_name ?? 'SWE' }}</div>
                                <div class="text-xs text-slate-400">{{ $emp->employee->employeeDetail?->department?->department_name ?? 'Engineering' }}</div>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <div class="text-slate-200 font-semibold">{{ $emp->paid_days }} / {{ $run->total_working_days }}</div>
                                <div class="text-xs text-rose-400">LOP: {{ $emp->lop_days }} days</div>
                            </td>
                            <td class="py-4 px-6 font-mono text-xs">
                                ₹{{ number_format($emp->monthly_gross_salary, 2) }}
                            </td>
                            <td class="py-4 px-6 font-mono text-xs text-emerald-400">
                                ₹{{ number_format($emp->total_earnings, 2) }}
                            </td>
                            <td class="py-4 px-6 font-mono text-xs text-rose-400">
                                ₹{{ number_format($emp->total_deductions, 2) }}
                            </td>
                            <td class="py-4 px-6 font-mono font-bold text-slate-200">
                                ₹{{ number_format($emp->net_salary, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-500">
                                No employee payroll entries compiled in this run.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
