@extends('layouts.app')

@section('title', 'Salary Structures')
@section('page_title', 'Salary Blueprint Settings')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header with Actions -->
    <div class="flex justify-between items-center bg-white/5 border border-white/10 rounded-3xl p-6 backdrop-blur-md">
        <div>
            <h2 class="text-xl font-bold text-slate-200">Salary Blueprint Configurations</h2>
            <p class="text-sm text-slate-400">Configure salary structures, allowances, deductions, and active formulas.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('payroll.index') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-semibold border border-white/5 transition duration-200">
                Back to Runs
            </a>
            <a href="{{ route('salary-structures.assign') }}" class="px-5 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-semibold rounded-xl text-xs shadow-lg shadow-indigo-500/25 transition duration-200">
                Assign structure to Employee
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Add New Structure Blueprint Form -->
        <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-6 shadow-2xl space-y-4">
            <h3 class="text-md font-semibold text-slate-200 border-b border-white/5 pb-2">Add Structure Blueprint</h3>
            <form action="{{ route('salary-structures.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Structure Name</label>
                    <input type="text" name="name" id="name" required class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm" placeholder="e.g. Executive Staff Structure">
                </div>
                <div>
                    <label for="description" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Description</label>
                    <textarea name="description" id="description" rows="3" class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm" placeholder="Explain scope or groups covered..."></textarea>
                </div>
                <button type="submit" class="w-full py-3 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-semibold rounded-2xl text-xs shadow-lg shadow-indigo-500/25 transition">
                    Create Blueprint
                </button>
            </form>
        </div>

        <!-- Structures & Components Setup Grid -->
        <div class="lg:col-span-2 space-y-6">
            @foreach($structures as $struct)
                <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-6 shadow-2xl space-y-6">
                    <div class="flex justify-between items-start border-b border-white/5 pb-3">
                        <div>
                            <h3 class="text-md font-bold text-slate-200">{{ $struct->name }}</h3>
                            <p class="text-xs text-slate-400 mt-1">{{ $struct->description ?? 'No description provided.' }}</p>
                        </div>
                    </div>

                    <!-- Components list in Structure -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-semibold text-indigo-400 uppercase tracking-wider">Associated Components Ratio</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($struct->components as $comp)
                                <div class="flex justify-between items-center p-3 bg-slate-900/40 rounded-2xl border border-white/5 text-xs">
                                    <span class="text-slate-300">{{ $comp->component_name }} ({{ $comp->component_code }})</span>
                                    <strong class="text-slate-200 font-mono">
                                        @if($comp->calculation_type === 'percentage_of_gross')
                                            {{ $comp->pivot->calculation_value }}% of Gross
                                        @elseif($comp->calculation_type === 'percentage_of_basic')
                                            {{ $comp->pivot->calculation_value }}% of Basic
                                        @elseif($comp->calculation_type === 'fixed')
                                            ₹{{ number_format($comp->pivot->calculation_value, 2) }}
                                        @else
                                            Slab / Engine Custom
                                        @endif
                                    </strong>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Manage Component Pivot Form -->
                    <form action="{{ route('salary-structures.components.update', $struct->id) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <h4 class="text-xs font-semibold text-slate-300 uppercase tracking-wider">Modify Component Allocations</h4>
                        <div class="space-y-2 max-h-60 overflow-y-auto pr-2">
                            @foreach($components as $index => $comp)
                                @php 
                                    $pivot = $struct->components->first(fn($c) => $c->id === $comp->id)?->pivot;
                                    $hasComp = !is_null($pivot);
                                @endphp
                                <div class="flex items-center justify-between p-2 bg-slate-900/20 rounded-xl border border-white/5 text-xs">
                                    <label class="flex items-center gap-2 text-slate-300 cursor-pointer">
                                        <input type="checkbox" name="components[{{ $index }}][id]" value="{{ $comp->id }}" {{ $hasComp ? 'checked' : '' }} class="rounded border-white/10 bg-slate-950 text-indigo-600 focus:ring-indigo-500">
                                        <span>{{ $comp->component_name }} ({{ $comp->component_code }})</span>
                                    </label>
                                    <div class="flex items-center gap-3">
                                        <input type="number" step="0.01" name="components[{{ $index }}][calculation_value]" value="{{ $pivot ? $pivot->calculation_value : $comp->default_value }}" placeholder="Value" class="w-20 bg-slate-900/80 border border-white/10 rounded-lg py-1 px-2 text-slate-200 text-center text-xs">
                                        <input type="number" name="components[{{ $index }}][sort_order]" value="{{ $pivot ? $pivot->sort_order : $index }}" placeholder="Order" class="w-12 bg-slate-900/80 border border-white/10 rounded-lg py-1 px-2 text-slate-200 text-center text-xs">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow transition">
                            Save Blueprint Allocations
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
