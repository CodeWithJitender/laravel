@extends('layouts.app')

@section('title', 'Request Attendance Correction')
@section('page_title', 'Request Attendance Correction')

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Back Link -->
    <div class="mb-6">
        <a href="{{ route('attendance.corrections.index') }}" 
           class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-white/5 font-semibold rounded-xl text-xs transition duration-200 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Requests
        </a>
    </div>

    <!-- Alert Messages -->
    @if(session('error'))
        <div class="mb-6 p-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-2xl text-sm">
            {{ session('error') }}
        </div>
    @endif

    <!-- Form Card -->
    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-8 shadow-2xl">
        <form action="{{ route('attendance.corrections.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Date Select -->
            <div>
                <label for="requested_date" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Target Date</label>
                <input type="date" name="requested_date" id="requested_date" max="{{ date('Y-m-d') }}" 
                       value="{{ old('requested_date', $date) }}"
                       class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-2.5 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm @error('requested_date') border-rose-500 focus:ring-rose-500 @enderror">
                @error('requested_date')
                    <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Existing Record Reference Box -->
            @if($attendance)
                <div class="p-4 bg-indigo-500/5 border border-indigo-500/10 rounded-2xl">
                    <span class="block text-[10px] font-bold text-indigo-400 uppercase tracking-wider mb-2">Existing System Record For This Date</span>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-slate-400">Current In:</span> 
                            <span class="font-mono text-slate-200 font-semibold">{{ $attendance->clock_in ? $attendance->clock_in->format('h:i A') : 'No Clock In' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400">Current Out:</span> 
                            <span class="font-mono text-slate-200 font-semibold">{{ $attendance->clock_out ? $attendance->clock_out->format('h:i A') : 'No Clock Out' }}</span>
                        </div>
                        <div class="col-span-2">
                            <span class="text-slate-400">Current Status:</span> 
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-lg uppercase tracking-wider border bg-indigo-500/10 text-indigo-400 border-indigo-500/20">{{ $attendance->attendance_status }}</span>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Punch In & Out Times -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="requested_clock_in" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Requested Clock In Time</label>
                    <input type="time" name="requested_clock_in" id="requested_clock_in" 
                           value="{{ old('requested_clock_in', $attendance && $attendance->clock_in ? $attendance->clock_in->format('H:i') : '') }}"
                           class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-2.5 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm @error('requested_clock_in') border-rose-500 focus:ring-rose-500 @enderror">
                    @error('requested_clock_in')
                        <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="requested_clock_out" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Requested Clock Out Time</label>
                    <input type="time" name="requested_clock_out" id="requested_clock_out" 
                           value="{{ old('requested_clock_out', $attendance && $attendance->clock_out ? $attendance->clock_out->format('H:i') : '') }}"
                           class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-2.5 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm @error('requested_clock_out') border-rose-500 focus:ring-rose-500 @enderror">
                    @error('requested_clock_out')
                        <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Reason -->
            <div>
                <label for="reason" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Reason for Adjustment</label>
                <textarea name="reason" id="reason" rows="4" placeholder="Explain why you are requesting this adjustment (min 10 characters)..."
                          class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-2.5 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm @error('reason') border-rose-500 focus:ring-rose-500 @enderror">{{ old('reason') }}</textarea>
                @error('reason')
                    <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Attachment -->
            <div>
                <label for="attachment" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Supporting Document (Optional)</label>
                <input type="file" name="attachment" id="attachment" accept="image/*,.pdf"
                       class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-2.5 px-4 text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm file:mr-4 file:py-1 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-600/10 file:text-indigo-400 hover:file:bg-indigo-600/20 file:cursor-pointer @error('attachment') border-rose-500 focus:ring-rose-500 @enderror">
                <span class="block text-[10px] text-slate-500 mt-1">Allowed formats: PDF, JPG, PNG. Max size: 2MB.</span>
                @error('attachment')
                    <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end gap-3 border-t border-white/5 pt-6">
                <a href="{{ route('attendance.corrections.index') }}" 
                   class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 border border-white/5 font-semibold rounded-xl text-xs transition duration-200 cursor-pointer">
                    Cancel
                </a>
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-xs transition duration-200 cursor-pointer">
                    Submit Request
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
