@extends('layouts.app')

@section('title', 'Create Shift')
@section('page_title', 'Create Shift')

@section('content')
<div class="max-w-3xl mx-auto">

    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-8 shadow-2xl relative overflow-hidden mb-6">
        <div class="absolute top-[-20%] left-[-10%] w-[50%] h-[50%] rounded-full bg-indigo-500/5 blur-[80px] pointer-events-none"></div>

        <h2 class="text-xl font-semibold mb-6 text-slate-200">Shift Configurations</h2>

        @if($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-sm">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('shifts.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="shift_code" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Shift Code</label>
                    <input type="text" name="shift_code" id="shift_code" required value="{{ old('shift_code') }}"
                        class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm" 
                        placeholder="e.g. MOR-S">
                </div>

                <div>
                    <label for="shift_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Shift Name</label>
                    <input type="text" name="shift_name" id="shift_name" required value="{{ old('shift_name') }}"
                        class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm" 
                        placeholder="e.g. Morning Shift">
                </div>
            </div>

            <div>
                <label for="description" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Description</label>
                <textarea name="description" id="description" rows="3"
                    class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm" 
                    placeholder="Provide details about the shift..."></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="start_time" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Start Time</label>
                    <input type="time" name="start_time" id="start_time" required value="{{ old('start_time') }}"
                        class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm">
                </div>

                <div>
                    <label for="end_time" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">End Time</label>
                    <input type="time" name="end_time" id="end_time" required value="{{ old('end_time') }}"
                        class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="grace_period_minutes" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Grace Period (Minutes)</label>
                    <input type="number" name="grace_period_minutes" id="grace_period_minutes" required value="{{ old('grace_period_minutes', 15) }}"
                        class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm" 
                        placeholder="15">
                </div>

                <div>
                    <label for="break_minutes" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Break Duration (Minutes)</label>
                    <input type="number" name="break_minutes" id="break_minutes" required value="{{ old('break_minutes', 60) }}"
                        class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm" 
                        placeholder="60">
                </div>

                <div>
                    <label for="status" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Status</label>
                    <select name="status" id="status" required
                        class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm select-dark">
                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-white/5">
                <a href="{{ route('shifts.index') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-semibold border border-white/5 transition duration-200">
                    Cancel
                </a>
                <button type="submit" 
                    class="px-5 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-semibold rounded-xl text-xs shadow-lg shadow-indigo-500/25 transition duration-200 transform hover:-translate-y-0.5 cursor-pointer">
                    Save Shift
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
