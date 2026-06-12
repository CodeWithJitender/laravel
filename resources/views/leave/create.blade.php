@extends('layouts.app')

@section('title', 'Apply for Leave')
@section('page_title', 'Apply for Leave')

@section('content')
<div class="max-w-4xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Form Column -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Back Link -->
        <div>
            <a href="{{ route('leave.index') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-white/5 font-semibold rounded-xl text-xs transition duration-200 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Dashboard
            </a>
        </div>

        @if(session('error'))
            <div class="p-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-2xl text-sm shadow-sm">
                {{ session('error') }}
            </div>
        @endif

        <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-8 shadow-2xl">
            <form action="{{ route('leave.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" id="leave-form">
                @csrf

                <!-- Leave Type Selector -->
                <div>
                    <label for="leave_type_id" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Leave Category</label>
                    <select name="leave_type_id" id="leave_type_id" required onchange="updateBalanceInfo()"
                            class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-2.5 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm select-dark @error('leave_type_id') border-rose-500 @enderror">
                        <option value="">Select leave category...</option>
                        @foreach($leaveTypes as $type)
                            <option value="{{ $type->id }}" data-balance="{{ $balances[$type->id] ?? 0.00 }}" data-color="{{ $type->color }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                    @error('leave_type_id')
                        <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Date Range Selection -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="start_date" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Start Date</label>
                        <input type="date" name="start_date" id="start_date" required min="{{ date('Y-m-d') }}"
                               value="{{ old('start_date') }}" onchange="calculateDuration()"
                               class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-2.5 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm @error('start_date') border-rose-500 @enderror">
                        @error('start_date')
                            <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="end_date" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">End Date</label>
                        <input type="date" name="end_date" id="end_date" required min="{{ date('Y-m-d') }}"
                               value="{{ old('end_date') }}" onchange="calculateDuration()"
                               class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-2.5 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm @error('end_date') border-rose-500 @enderror">
                        @error('end_date')
                            <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Half Day Section -->
                <div class="p-4 bg-slate-950/40 rounded-2xl border border-white/5 space-y-4">
                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="half_day" id="half_day" value="1" {{ old('half_day') ? 'checked' : '' }}
                               onchange="toggleHalfDay()"
                               class="w-4 h-4 rounded bg-slate-900 border-white/10 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-slate-950">
                        <label for="half_day" class="text-sm font-semibold text-slate-300">Request Half Day Session</label>
                    </div>

                    <div id="half_day_session_container" class="hidden">
                        <label for="half_day_session" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Session Type</label>
                        <select name="half_day_session" id="half_day_session"
                                class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-2.5 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm select-dark">
                            <option value="first_half" {{ old('half_day_session') == 'first_half' ? 'selected' : '' }}>First Half (Morning)</option>
                            <option value="second_half" {{ old('half_day_session') == 'second_half' ? 'selected' : '' }}>Second Half (Afternoon)</option>
                        </select>
                    </div>
                </div>

                <!-- Emergency Contact Phone -->
                <div>
                    <label for="emergency_phone" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Emergency Contact Number</label>
                    <input type="tel" name="emergency_phone" id="emergency_phone" required placeholder="e.g. +91 98765 43210"
                           value="{{ old('emergency_phone') }}"
                           oninput="this.value = this.value.replace(/[^0-9+\-\s()]/g, '');"
                           class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-2.5 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm @error('emergency_phone') border-rose-500 @enderror">
                    @error('emergency_phone')
                        <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Reason for request -->
                <div>
                    <label for="reason" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Reason for Leave</label>
                    <textarea name="reason" id="reason" rows="4" placeholder="Briefly describe the reason for your leave request (min 10 characters)..." required
                              class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-2.5 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm @error('reason') border-rose-500 @enderror">{{ old('reason') }}</textarea>
                    @error('reason')
                        <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Supporting Attachment -->
                <div>
                    <label for="attachment" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Supporting Document (Optional)</label>
                    <input type="file" name="attachment" id="attachment" accept="image/*,.pdf"
                           class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-2.5 px-4 text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm file:mr-4 file:py-1 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-600/10 file:text-indigo-400 hover:file:bg-indigo-600/20 file:cursor-pointer @error('attachment') border-rose-500 @enderror">
                    <span class="block text-[10px] text-slate-500 mt-1">Allowed formats: PDF, JPG, PNG. Max size: 2MB.</span>
                    @error('attachment')
                        <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Action Button Row -->
                <div class="flex justify-end gap-3 border-t border-white/5 pt-6">
                    <a href="{{ route('leave.index') }}" 
                       class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 border border-white/5 font-semibold rounded-xl text-xs transition duration-200 cursor-pointer">
                        Cancel
                    </a>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-xs transition duration-200 cursor-pointer shadow-lg shadow-indigo-500/10">
                        Submit Leave Application
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Live Preview Sidebar Column -->
    <div class="space-y-6">
        <!-- Live Metrics Box -->
        <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-6 shadow-2xl">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 border-b border-white/5 pb-2">Application Summary</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-400">Selected Days:</span>
                    <span class="text-lg font-bold font-mono text-indigo-400" id="preview-days">0.0 days</span>
                </div>
                
                <div id="balance-info-container" class="hidden space-y-2 pt-2 border-t border-white/5">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-400">Available Balance:</span>
                        <span class="font-bold font-mono text-slate-200" id="preview-balance">0.0 days</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-400">New Balance after Leave:</span>
                        <span class="font-bold font-mono text-slate-200" id="preview-new-balance">0.0 days</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Rules Card -->
        <div class="backdrop-blur-md bg-white/5 border border-indigo-500/10 rounded-3xl p-6 shadow-2xl">
            <h4 class="text-xs font-bold text-indigo-400 uppercase tracking-wider mb-2 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Important Guidelines
            </h4>
            <ul class="text-[11px] text-slate-400 space-y-2 list-disc list-inside">
                <li>Leave balances are verified instantly at submission.</li>
                <li>Weekends are automatically excluded from the day calculations if they are your shift weekly-offs.</li>
                <li>Attachments are required for Sick Leave if duration exceeds 2 days.</li>
            </ul>
        </div>
    </div>
</div>

<script>
    function toggleHalfDay() {
        const halfDayChecked = document.getElementById('half_day').checked;
        const sessionContainer = document.getElementById('half_day_session_container');
        const endDateInput = document.getElementById('end_date');
        const startDateInput = document.getElementById('start_date');

        if (halfDayChecked) {
            sessionContainer.classList.remove('hidden');
            // For half day, end date must match start date
            if (startDateInput.value) {
                endDateInput.value = startDateInput.value;
                endDateInput.disabled = true;
                // Add hidden input to submit end_date if disabled
                if (!document.getElementById('hidden_end_date')) {
                    const hiddenEnd = document.createElement('input');
                    hiddenEnd.type = 'hidden';
                    hiddenEnd.name = 'end_date';
                    hiddenEnd.id = 'hidden_end_date';
                    hiddenEnd.value = startDateInput.value;
                    document.getElementById('leave-form').appendChild(hiddenEnd);
                } else {
                    document.getElementById('hidden_end_date').value = startDateInput.value;
                }
            }
        } else {
            sessionContainer.classList.add('hidden');
            endDateInput.disabled = false;
            const hiddenEnd = document.getElementById('hidden_end_date');
            if (hiddenEnd) {
                hiddenEnd.remove();
            }
        }
        calculateDuration();
    }

    function updateBalanceInfo() {
        const select = document.getElementById('leave_type_id');
        const container = document.getElementById('balance-info-container');
        const selectedOption = select.options[select.selectedIndex];

        if (selectedOption && selectedOption.value) {
            container.classList.remove('hidden');
            const balance = parseFloat(selectedOption.getAttribute('data-balance'));
            document.getElementById('preview-balance').innerText = balance.toFixed(1) + ' days';
            calculateDuration();
        } else {
            container.classList.add('hidden');
        }
    }

    function calculateDuration() {
        const startDateVal = document.getElementById('start_date').value;
        const endDateVal = document.getElementById('end_date').disabled 
            ? document.getElementById('start_date').value 
            : document.getElementById('end_date').value;
        const halfDayChecked = document.getElementById('half_day').checked;

        if (!startDateVal || !endDateVal) {
            document.getElementById('preview-days').innerText = '0.0 days';
            return;
        }

        const start = new Date(startDateVal);
        const end = new Date(endDateVal);
        
        if (end < start) {
            document.getElementById('preview-days').innerText = 'Invalid range';
            return;
        }

        let workingDays = 0;
        let current = new Date(start);

        while (current <= end) {
            const day = current.getDay();
            // Default check: exclude Saturday (6) and Sunday (0)
            if (day !== 0 && day !== 6) {
                workingDays++;
            }
            current.setDate(current.getDate() + 1);
        }

        let totalDays = workingDays;
        if (halfDayChecked) {
            totalDays = workingDays * 0.5;
        }

        document.getElementById('preview-days').innerText = totalDays.toFixed(1) + ' days';

        // Update post-leave balance calculation
        const select = document.getElementById('leave_type_id');
        const selectedOption = select.options[select.selectedIndex];
        if (selectedOption && selectedOption.value) {
            const balance = parseFloat(selectedOption.getAttribute('data-balance'));
            const newBalance = balance - totalDays;
            const newBalPreview = document.getElementById('preview-new-balance');
            newBalPreview.innerText = newBalance.toFixed(1) + ' days';
            
            if (newBalance < 0) {
                newBalPreview.classList.add('text-rose-400');
                newBalPreview.classList.remove('text-slate-200');
            } else {
                newBalPreview.classList.remove('text-rose-400');
                newBalPreview.classList.add('text-slate-200');
            }
        }
    }

    // Run initialize calculations if returning values on error redirect
    document.addEventListener('DOMContentLoaded', () => {
        toggleHalfDay();
        updateBalanceInfo();
        
        const form = document.getElementById('leave-form');
        form.addEventListener('submit', (e) => {
            const startDateVal = document.getElementById('start_date').value;
            const endDateVal = document.getElementById('end_date').disabled 
                ? document.getElementById('start_date').value 
                : document.getElementById('end_date').value;
            if (startDateVal && endDateVal) {
                const start = new Date(startDateVal);
                const end = new Date(endDateVal);
                if (end < start) {
                    e.preventDefault();
                    alert('End Date must be greater than or equal to Start Date.');
                }
            }
        });
    });
</script>
@endsection
