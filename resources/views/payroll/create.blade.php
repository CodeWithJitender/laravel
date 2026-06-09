@extends('layouts.app')

@section('title', 'Start Payroll Run')
@section('page_title', 'Initialize Payroll Run')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-8 shadow-2xl relative overflow-hidden">
        <div class="absolute top-[-20%] left-[-10%] w-[50%] h-[50%] rounded-full bg-indigo-500/5 blur-[80px] pointer-events-none"></div>

        <h2 class="text-xl font-semibold mb-6 text-slate-200">Start Payroll Cycle Run</h2>

        @if($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-sm">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('payroll.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Month Selection -->
            <div>
                <label for="month" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Target Month</label>
                <select name="month" id="month" required class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ old('month', date('n')) == $m ? 'selected' : '' }}>
                            {{ Carbon\Carbon::create(null, $m, 1)->format('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Year Selection -->
            <div>
                <label for="year" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Target Year</label>
                <select name="year" id="year" required class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm">
                    @foreach(range(date('Y') - 1, date('Y') + 1) as $y)
                        <option value="{{ $y }}" {{ old('year', date('Y')) == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Run Type -->
            <div>
                <label for="run_type" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Run Type</label>
                <select name="run_type" id="run_type" required class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm">
                    <option value="monthly" {{ old('run_type') == 'monthly' ? 'selected' : '' }}>Monthly Run (Standard)</option>
                    <option value="off_cycle" {{ old('run_type') == 'off_cycle' ? 'selected' : '' }}>Off-Cycle Run</option>
                    <option value="bonus" {{ old('run_type') == 'bonus' ? 'selected' : '' }}>Bonus Release</option>
                    <option value="adjustment" {{ old('run_type') == 'adjustment' ? 'selected' : '' }}>Adjustment Run</option>
                </select>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-white/5">
                <a href="{{ route('payroll.index') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-semibold border border-white/5 transition duration-200">
                    Cancel
                </a>
                <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-semibold rounded-xl text-xs shadow-lg shadow-indigo-500/25 transition duration-200">
                    Start Calculations
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
