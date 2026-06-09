@extends('layouts.app')

@section('title', 'Holiday Management Console')
@section('page_title', 'Holidays Dashboard')

@section('content')
<div class="space-y-6" x-data="{ openModal: false, editMode: false, currentHoliday: null, selectedLocations: [] }">
    <!-- Header Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <p class="text-sm text-slate-400">Define national, regional, and location-specific holidays. Manage visibility states and publication workflows.</p>
        </div>
        <div class="flex items-center gap-3">
            @if(auth()->user()->hasAnyRole(['Admin', 'Manager']))
                <button @click="openModal = true; editMode = false; currentHoliday = null; selectedLocations = []" class="px-4 py-2 text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl shadow-lg shadow-indigo-500/20 transition cursor-pointer">
                    New Holiday
                </button>
            @endif
            <a href="{{ route('holidays.calendar') }}" class="px-4 py-2 text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-xl transition cursor-pointer">
                Calendar View
            </a>
            <a href="{{ route('holidays.reports') }}" class="px-4 py-2 text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-xl transition cursor-pointer">
                Reports Board
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl relative overflow-hidden">
            <div class="absolute top-0 right-0 w-16 h-16 rounded-full bg-indigo-500/5 blur-xl"></div>
            <span class="block text-xs font-bold text-slate-500 uppercase tracking-widest">Total Holidays</span>
            <span class="block text-2xl font-bold text-slate-100 mt-2">{{ count($holidays) }}</span>
            <span class="block text-[10px] text-slate-400 mt-1">For year {{ $filters['year'] ?? date('Y') }}</span>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl relative overflow-hidden">
            <div class="absolute top-0 right-0 w-16 h-16 rounded-full bg-emerald-500/5 blur-xl"></div>
            <span class="block text-xs font-bold text-slate-500 uppercase tracking-widest">National Holidays</span>
            <span class="block text-2xl font-bold text-emerald-400 mt-2">
                {{ $holidays->filter(fn($h) => $h->holidayType->code === 'national')->count() }}
            </span>
            <span class="block text-[10px] text-slate-400 mt-1">Observed country-wide</span>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl relative overflow-hidden">
            <div class="absolute top-0 right-0 w-16 h-16 rounded-full bg-rose-500/5 blur-xl"></div>
            <span class="block text-xs font-bold text-slate-500 uppercase tracking-widest">Draft States</span>
            <span class="block text-2xl font-bold text-rose-400 mt-2">
                {{ $holidays->filter(fn($h) => $h->status === 'draft')->count() }}
            </span>
            <span class="block text-[10px] text-slate-400 mt-1">Pending publication</span>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl">
        <form action="{{ route('holidays.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Filter Year</label>
                <select name="year" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-indigo-500 transition">
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ ($filters['year'] ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Physical Location</label>
                <select name="location_id" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-indigo-500 transition">
                    <option value="">All Locations (National)</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ ($filters['location_id'] ?? '') == $loc->id ? 'selected' : '' }}>{{ $loc->location_name }} ({{ $loc->location_code }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Holiday Category</label>
                <select name="holiday_type_id" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-indigo-500 transition">
                    <option value="">All Types</option>
                    @foreach($holidayTypes as $type)
                        <option value="{{ $type->id }}" {{ ($filters['holiday_type_id'] ?? '') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-grow px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl font-semibold text-sm transition cursor-pointer">
                    Apply Filters
                </button>
                <a href="{{ route('holidays.index') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl font-semibold text-sm border border-slate-700 transition flex items-center justify-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Holidays List Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        @if($holidays->isEmpty())
            <div class="p-16 text-center">
                <div class="w-16 h-16 bg-slate-800/50 rounded-2xl flex items-center justify-center mx-auto text-slate-500 border border-slate-800 mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-300">No holidays defined</h3>
                <p class="text-sm text-slate-500 mt-1">There are no holidays matching the chosen filters.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-800 text-[11px] font-bold text-slate-400 uppercase bg-slate-900/50">
                            <th class="p-4 pl-6">Holiday Details</th>
                            <th class="p-4">Date</th>
                            <th class="p-4">Scope / Location</th>
                            <th class="p-4">Paid / Compensation</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 pr-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/50">
                        @foreach($holidays as $holiday)
                            <tr class="hover:bg-slate-800/10 transition">
                                <td class="p-4 pl-6">
                                    <span class="font-bold text-slate-200 block text-sm">{{ $holiday->holiday_name }}</span>
                                    <div class="flex items-center gap-2 mt-1">
                                        <code class="text-[10px] text-indigo-400 bg-indigo-500/5 px-1.5 py-0.5 rounded border border-indigo-500/10 font-mono">{{ $holiday->holiday_code }}</code>
                                        <span class="text-xs text-slate-500">{{ $holiday->holidayType->name }}</span>
                                    </div>
                                </td>
                                <td class="p-4 text-sm text-slate-300">
                                    <span class="font-semibold">{{ $holiday->holiday_date->format('M d, Y') }}</span>
                                    <span class="block text-xs text-slate-500 mt-0.5">{{ $holiday->holiday_date->format('l') }}</span>
                                </td>
                                <td class="p-4">
                                    @if($holiday->locations->isEmpty())
                                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded bg-indigo-600/10 text-indigo-400 border border-indigo-500/20">
                                            National / Company
                                        </span>
                                    @else
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($holiday->locations as $loc)
                                                <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded bg-slate-800 text-slate-400 border border-slate-700">
                                                    {{ $loc->location_code }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="p-4">
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full border block w-max
                                        {{ $holiday->is_paid ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/20' }}">
                                        {{ $holiday->is_paid ? 'Paid' : 'Unpaid' }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full 
                                        @if($holiday->status === 'published') bg-emerald-600/10 text-emerald-400 border border-emerald-500/20
                                        @elseif($holiday->status === 'draft') bg-amber-500/10 text-amber-400 border border-amber-500/20
                                        @else bg-slate-800 text-slate-500 border border-slate-700
                                        @endif">
                                        {{ ucfirst($holiday->status) }}
                                    </span>
                                </td>
                                <td class="p-4 pr-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($holiday->status === 'draft' && auth()->user()->hasAnyRole(['Admin', 'Manager']))
                                            <form action="{{ route('holidays.publish', $holiday->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-2 py-1 text-[11px] font-bold bg-emerald-600/10 text-emerald-400 border border-emerald-500/20 hover:bg-emerald-600 hover:text-white rounded-lg transition cursor-pointer">
                                                    Publish
                                                </button>
                                            </form>
                                        @endif
                                        @if(auth()->user()->hasAnyRole(['Admin', 'Manager']))
                                            <button 
                                                @click="
                                                    editMode = true; 
                                                    currentHoliday = {{ json_encode($holiday) }};
                                                    selectedLocations = {{ json_encode($holiday->locations->pluck('id')) }};
                                                    openModal = true;
                                                " 
                                                class="p-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 rounded-lg transition cursor-pointer"
                                                title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                                </svg>
                                            </button>
                                            <form action="{{ route('holidays.destroy', $holiday->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this holiday?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 bg-slate-800 hover:bg-rose-950 text-slate-400 hover:text-rose-400 border border-slate-700 hover:border-rose-900 rounded-lg transition cursor-pointer" title="Delete">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($holidays->hasPages())
                <div class="p-6 border-t border-slate-800 bg-slate-900/50">
                    {{ $holidays->links() }}
                </div>
            @endif
        @endif
    </div>

    <!-- Create / Edit Modal -->
    <div 
        x-show="openModal" 
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm p-4"
        x-transition
        style="display: none;"
    >
        <div 
            @click.outside="openModal = false" 
            class="bg-slate-900 border border-slate-800 rounded-2xl max-w-xl w-full p-6 overflow-hidden shadow-2xl relative"
        >
            <div class="flex items-center justify-between pb-4 border-b border-slate-800 mb-4">
                <h3 class="text-lg font-bold text-slate-100" x-text="editMode ? 'Edit Holiday' : 'New Holiday'"></h3>
                <button @click="openModal = false" class="text-slate-400 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Form -->
            <form :action="editMode ? `/holidays/${currentHoliday.id}` : '/holidays'" method="POST" class="space-y-4">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Holiday Name</label>
                        <input 
                            type="text" 
                            name="holiday_name" 
                            required 
                            :value="currentHoliday ? currentHoliday.holiday_name : ''"
                            class="w-full bg-slate-800/50 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 transition"
                        >
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Holiday Code</label>
                        <input 
                            type="text" 
                            name="holiday_code" 
                            required 
                            :value="currentHoliday ? currentHoliday.holiday_code : ''"
                            class="w-full bg-slate-800/50 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 transition"
                        >
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Date</label>
                        <input 
                            type="date" 
                            name="holiday_date" 
                            required 
                            :value="currentHoliday ? currentHoliday.holiday_date.split('T')[0] : ''"
                            class="w-full bg-slate-800/50 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 transition"
                        >
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Holiday Type</label>
                        <select 
                            name="holiday_type_id" 
                            required 
                            class="w-full bg-slate-800/50 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 transition"
                        >
                            @foreach($holidayTypes as $type)
                                <option value="{{ $type->id }}" :selected="currentHoliday && currentHoliday.holiday_type_id == {{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Scope (Locations)</label>
                        <select 
                            name="location_ids[]" 
                            multiple
                            class="w-full bg-slate-800/50 border border-slate-700 rounded-xl px-4 py-2 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 transition"
                        >
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" :selected="selectedLocations.includes({{ $loc->id }})">{{ $loc->location_name }}</option>
                            @endforeach
                        </select>
                        <span class="block text-[10px] text-slate-500 mt-1">Leave empty to apply to all locations (National).</span>
                    </div>

                    <div class="space-y-4">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Settings</label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 cursor-pointer text-sm text-slate-300">
                                <input 
                                    type="checkbox" 
                                    name="is_paid" 
                                    value="1" 
                                    :checked="currentHoliday ? currentHoliday.is_paid : true"
                                    class="rounded bg-slate-800 border-slate-700 text-indigo-600 focus:ring-0"
                                >
                                Paid Holiday
                            </label>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Status</label>
                        <select 
                            name="status" 
                            required 
                            class="w-full bg-slate-800/50 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 transition"
                        >
                            <option value="draft" :selected="currentHoliday && currentHoliday.status === 'draft'">Draft</option>
                            <option value="published" :selected="currentHoliday && currentHoliday.status === 'published'">Published</option>
                            <option value="cancelled" :selected="currentHoliday && currentHoliday.status === 'cancelled'">Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Visibility Alert</label>
                        <label class="flex items-center gap-2 cursor-pointer text-sm text-slate-300 mt-2">
                            <input 
                                type="checkbox" 
                                name="notify_employees" 
                                value="1" 
                                checked
                                class="rounded bg-slate-800 border-slate-700 text-indigo-600 focus:ring-0"
                            >
                            Notify Employees immediately
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Description</label>
                    <textarea 
                        name="description" 
                        rows="3" 
                        x-text="currentHoliday ? currentHoliday.description : ''"
                        class="w-full bg-slate-800/50 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 transition resize-none"
                    ></textarea>
                </div>

                <div class="pt-4 border-t border-slate-800 flex justify-end gap-3">
                    <button type="button" @click="openModal = false" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 rounded-xl transition cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl shadow-lg shadow-indigo-500/20 transition cursor-pointer">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- AlpineJS -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endsection
