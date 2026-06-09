@extends('layouts.app')

@section('title', 'Holiday Utilization Report')
@section('page_title', 'Holiday Reports')

@section('content')
<div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <p class="text-sm text-slate-400">Review yearly statistics, scope allocations, and category distributions of company holidays.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('holidays.index') }}" class="px-4 py-2 text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-xl transition cursor-pointer">
                Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl relative overflow-hidden">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Total Schedule Holidays</h3>
            <span class="text-3xl font-extrabold text-slate-100">{{ $totalHolidays }} Days</span>
            <p class="text-xs text-slate-400 mt-2">Active holidays defined in the calendar year {{ $year }}.</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl relative overflow-hidden">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">National Mandatories</h3>
            <span class="text-3xl font-extrabold text-emerald-400">{{ $nationalCount }} Days</span>
            <p class="text-xs text-slate-400 mt-2">Country-wide paid rest days observed by all employees.</p>
        </div>

    </div>

    <!-- Breakdown Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl p-6 space-y-4">
        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-200">Yearly Holiday Schedule Breakdowns - {{ $year }}</h3>
        
        @if($holidays->isEmpty())
            <p class="text-sm text-slate-500 italic text-center p-8">No holidays defined for this year.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-800 text-xs font-bold text-slate-400 uppercase bg-slate-900/50">
                            <th class="p-3 pl-4">Date</th>
                            <th class="p-3">Holiday Name</th>
                            <th class="p-3">Category</th>
                            <th class="p-3">Compensation type</th>
                            <th class="p-3 pr-4">Scope / Locations</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/50 text-sm">
                        @foreach($holidays as $h)
                            <tr class="hover:bg-slate-800/10 transition">
                                <td class="p-3 pl-4 text-xs font-mono text-slate-500">
                                    {{ $h->holiday_date->toDateString() }} ({{ $h->holiday_date->format('l') }})
                                </td>
                                <td class="p-3 font-semibold text-slate-200">
                                    {{ $h->holiday_name }}
                                </td>
                                <td class="p-3 text-slate-400">
                                    {{ $h->holidayType->name }}
                                </td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full border
                                        {{ $h->is_paid ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/20' }}">
                                        {{ $h->is_paid ? 'Paid' : 'Unpaid' }}
                                    </span>
                                </td>
                                <td class="p-3 pr-4">
                                    @if($h->locations->isEmpty())
                                        <span class="text-xs text-indigo-400 font-semibold">National / All Locations</span>
                                    @else
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($h->locations as $l)
                                                <span class="px-1.5 py-0.5 text-[9px] bg-slate-800 text-slate-400 rounded border border-slate-700 font-mono">
                                                    {{ $l->location_code }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
