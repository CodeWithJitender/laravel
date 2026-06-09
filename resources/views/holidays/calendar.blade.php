@extends('layouts.app')

@section('title', 'Holiday Calendar')
@section('page_title', 'Holiday Calendar')

@section('content')
<div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <p class="text-sm text-slate-400">View corporate and regional holidays mapped for physical locations.</p>
        </div>
        <div class="flex items-center gap-3">
            @if(auth()->user()->hasAnyRole(['Admin', 'Manager']))
                <a href="{{ route('holidays.index') }}" class="px-4 py-2 text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-xl transition cursor-pointer">
                    Holidays Console
                </a>
            @endif

        </div>
    </div>

    <!-- Calendar View Container -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Calendar Column -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Filter Bar -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl">
                <form action="{{ route('holidays.calendar') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Select Year</label>
                        <select name="year" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-indigo-500 transition">
                            @for($y = date('Y') - 2; $y <= date('Y') + 2; $y++)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Physical Location</label>
                        <select name="location_id" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-indigo-500 transition">
                            <option value="">All Locations (National)</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" {{ $locationId == $loc->id ? 'selected' : '' }}>{{ $loc->location_name }} ({{ $loc->location_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="w-full px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl font-semibold text-sm border border-slate-700 transition cursor-pointer">
                            Filter Calendar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Month Grid Card -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
                <div class="flex items-center justify-between pb-4 border-b border-slate-800 mb-6">
                    <h3 class="text-base font-bold text-slate-200">Yearly Holiday Schedule - {{ $year }}</h3>
                </div>

                @if($holidays->isEmpty())
                    <div class="p-12 text-center text-slate-500">
                        No active holidays found matching criteria.
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @php
                            $months = [
                                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                            ];
                        @endphp
                        @foreach($months as $monthNum => $monthName)
                            @php
                                $monthHolidays = $holidays->filter(fn($h) => $h->holiday_date->month == $monthNum);
                            @endphp
                            <div class="bg-slate-850/40 border border-slate-800/80 rounded-xl p-4 space-y-3">
                                <span class="block text-sm font-bold text-slate-300 border-b border-slate-800 pb-1.5">{{ $monthName }}</span>
                                @if($monthHolidays->isEmpty())
                                    <span class="block text-xs text-slate-600 italic">No holidays this month</span>
                                @else
                                    <div class="space-y-2">
                                        @foreach($monthHolidays as $mh)
                                            <div class="flex items-start justify-between gap-3 text-xs">
                                                <div class="min-w-0">
                                                    <span class="font-bold text-indigo-400 block">{{ $mh->holiday_date->format('d M') }}</span>
                                                    <span class="text-slate-200 truncate block font-medium" title="{{ $mh->holiday_name }}">{{ $mh->holiday_name }}</span>
                                                </div>

                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar List / Upcoming Holidays Column -->
        <div class="space-y-6">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-4">Upcoming Schedule</h3>
                @php
                    $upcoming = $holidays->filter(fn($h) => $h->holiday_date->isAfter(now()->subDay()))->slice(0, 5);
                @endphp
                @if($upcoming->isEmpty())
                    <p class="text-xs text-slate-500 italic">No upcoming holidays scheduled.</p>
                @else
                    <div class="space-y-3">
                        @foreach($upcoming as $uh)
                            <div class="p-3 bg-slate-800/30 border border-slate-800 rounded-xl flex items-start justify-between gap-3">
                                <div>
                                    <span class="block text-[10px] text-slate-500 font-bold uppercase">{{ $uh->holiday_date->diffForHumans() }}</span>
                                    <span class="block text-sm font-bold text-slate-200 mt-0.5">{{ $uh->holiday_name }}</span>
                                    <span class="block text-[10px] text-indigo-400 font-mono mt-1">{{ $uh->holiday_date->format('l, M d') }}</span>
                                </div>

                            </div>
                        @endforeach
                    </div>
                @endif
            </div>


        </div>
    </div>
</div>
@endsection
