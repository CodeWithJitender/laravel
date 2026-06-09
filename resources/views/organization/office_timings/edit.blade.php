@extends('layouts.app')

@section('title', 'Office Timings')
@section('page_title', 'Office Timing Policies')

@section('content')
<div class="max-w-4xl mx-auto">

    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-8 shadow-2xl relative overflow-hidden mb-6">
        <div class="absolute top-[-20%] left-[-10%] w-[50%] h-[50%] rounded-full bg-indigo-500/5 blur-[80px] pointer-events-none"></div>

        <h2 class="text-xl font-semibold mb-2 text-slate-200">Global Attendance & Timing Rules</h2>
        <p class="text-xs text-slate-400 mb-6">Configure corporate default working hours, attendance duration parameters, working days, and weekend flags.</p>

        <!-- Feedback Alerts -->
        @if(session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-sm">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('office-timings.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Configuration Name</label>
                    <input type="text" name="name" id="name" required value="{{ old('name', $timing->name) }}"
                        class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm" 
                        placeholder="e.g. Headquarters Schedule">
                </div>

                <div>
                    <label for="status" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Status</label>
                    <select name="status" id="status" required
                        class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm select-dark">
                        <option value="active" {{ old('status', $timing->status) == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $timing->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <!-- Working Days Selector -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">Company Working Days</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-3">
                    @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                        <label class="flex flex-col items-center justify-center p-3 bg-slate-900/40 rounded-xl border border-white/5 cursor-pointer select-none hover:bg-slate-900/60 transition duration-150">
                            <input type="checkbox" name="working_days[]" value="{{ $day }}" 
                                {{ in_array($day, old('working_days', $timing->working_days ?? [])) ? 'checked' : '' }}
                                class="rounded border-white/10 bg-slate-950 text-indigo-600 focus:ring-indigo-500 mb-2">
                            <span class="text-xs text-slate-300">{{ substr($day, 0, 3) }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Time Slot Windows -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="start_time" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Start Time</label>
                    <input type="time" name="start_time" id="start_time" required value="{{ old('start_time', substr($timing->start_time, 0, 5)) }}"
                        class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm">
                </div>

                <div>
                    <label for="end_time" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">End Time</label>
                    <input type="time" name="end_time" id="end_time" required value="{{ old('end_time', substr($timing->end_time, 0, 5)) }}"
                        class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm">
                </div>
            </div>

            <!-- Working Hours Rules -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="minimum_hours" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Minimum Hours (Full Day)</label>
                    <input type="number" step="0.25" name="minimum_hours" id="minimum_hours" required value="{{ old('minimum_hours', $timing->minimum_hours) }}"
                        class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm" 
                        placeholder="8.00">
                </div>

                <div>
                    <label for="half_day_hours" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Minimum Hours (Half Day)</label>
                    <input type="number" step="0.25" name="half_day_hours" id="half_day_hours" required value="{{ old('half_day_hours', $timing->half_day_hours) }}"
                        class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm" 
                        placeholder="4.00">
                </div>
            </div>

            <!-- Weekend Off Selector -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">Weekly Off Days</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-3">
                    @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                        <label class="flex flex-col items-center justify-center p-3 bg-slate-900/40 rounded-xl border border-white/5 cursor-pointer select-none hover:bg-slate-900/60 transition duration-150">
                            <input type="checkbox" name="weekly_off[]" value="{{ $day }}" 
                                {{ in_array($day, old('weekly_off', $timing->weekly_off ?? [])) ? 'checked' : '' }}
                                class="rounded border-white/10 bg-slate-950 text-indigo-600 focus:ring-indigo-500 mb-2">
                            <span class="text-xs text-slate-300">{{ substr($day, 0, 3) }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-white/5">
                <button type="submit" 
                    class="px-5 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-semibold rounded-xl text-xs shadow-lg shadow-indigo-500/25 transition duration-200 transform hover:-translate-y-0.5 cursor-pointer">
                    Save Rules & Configuration
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
