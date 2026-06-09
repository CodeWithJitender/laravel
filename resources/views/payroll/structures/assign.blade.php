@extends('layouts.app')

@section('title', 'Assign Salary Structure')
@section('page_title', 'Assign Salary Blueprint')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-8 shadow-2xl relative overflow-hidden">
        <div class="absolute top-[-20%] left-[-10%] w-[50%] h-[50%] rounded-full bg-indigo-500/5 blur-[80px] pointer-events-none"></div>

        <h2 class="text-xl font-semibold mb-6 text-slate-200">Assign Blueprint to Employee</h2>

        @if(session('error'))
            <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-sm">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('salary-structures.assign.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Employee Selection -->
            <div>
                <label for="employee_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Select Employee</label>
                <select name="employee_id" id="employee_id" required class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm">
                    <option value="">-- Choose Employee --</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->name }} (ID: EMP-{{ str_pad($emp->id, 4, '0', STR_PAD_LEFT) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Structure Blueprint Selection -->
            <div>
                <label for="salary_structure_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Select Structure Blueprint</label>
                <select name="salary_structure_id" id="salary_structure_id" required class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm">
                    <option value="">-- Choose Structure --</option>
                    @foreach($structures as $struct)
                        <option value="{{ $struct->id }}" {{ old('salary_structure_id') == $struct->id ? 'selected' : '' }}>
                            {{ $struct->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Monthly Gross Salary -->
            <div>
                <label for="monthly_gross_salary" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Monthly Gross Salary (₹)</label>
                <input type="number" step="0.01" name="monthly_gross_salary" id="monthly_gross_salary" required value="{{ old('monthly_gross_salary') }}" class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm" placeholder="e.g. 75000.00">
            </div>

            <!-- Effective From Date -->
            <div>
                <label for="effective_from" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Effective From</label>
                <input type="date" name="effective_from" id="effective_from" required value="{{ old('effective_from', date('Y-m-d')) }}" class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm">
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-white/5">
                <a href="{{ route('salary-structures.index') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-semibold border border-white/5 transition duration-200">
                    Cancel
                </a>
                <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-semibold rounded-xl text-xs shadow-lg shadow-indigo-500/25 transition duration-200">
                    Assign structure
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
