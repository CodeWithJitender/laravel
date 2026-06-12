@extends('layouts.app')

@section('title', 'Add Employee')
@section('page_title', 'Add New Employee')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">
    <!-- Top Action Banner -->
    <div class="flex justify-between items-center bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md">
        <div>
            <h2 class="text-xl font-bold text-white">Create Employee Profile</h2>
            <p class="text-slate-400 text-sm mt-1">Register a new user account and attach their employee database profile.</p>
        </div>
        <a href="{{ route('employees.index') }}" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-medium text-sm transition duration-200 cursor-pointer">
            Back to Directory
        </a>
    </div>

    <!-- Form -->
    <form action="{{ route('employees.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Section 1: Account Information -->
        <div class="bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md space-y-4">
            <h3 class="text-lg font-bold text-indigo-400 border-b border-white/10 pb-2 mb-4">Account Credentials</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Full Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition @error('name') border-rose-500 @enderror">
                    @error('name')
                        <span class="block text-rose-500 text-xs mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Email Address *</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                           class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition @error('email') border-rose-500 @enderror">
                    @error('email')
                        <span class="block text-rose-500 text-xs mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Password *</label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required
                               class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 pr-10 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition @error('password') border-rose-500 @enderror">
                        <button type="button" onclick="togglePasswordVisibility('password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white transition cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <span class="block text-rose-500 text-xs mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password Confirmation -->
                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Confirm Password *</label>
                    <div class="relative">
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                               class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 pr-10 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition">
                        <button type="button" onclick="togglePasswordVisibility('password_confirmation', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white transition cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Account Status *</label>
                    <select name="status" id="status" required
                            class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 transition">
                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <!-- Spatie Role -->
                <div>
                    <label for="role" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">System Role *</label>
                    <select name="role" id="role" required
                            class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 transition">
                        <option value="Employee" {{ old('role') == 'Employee' ? 'selected' : '' }}>Employee</option>
                        <option value="Manager" {{ old('role') == 'Manager' ? 'selected' : '' }}>Manager</option>
                        <option value="Admin" {{ old('role') == 'Admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Section 2: Placement & Work Details -->
        <div class="bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md space-y-4">
            <h3 class="text-lg font-bold text-indigo-400 border-b border-white/10 pb-2 mb-4">Work Placement & Schedule</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Employee Code -->
                <div>
                    <label for="employee_code" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Employee ID/Code *</label>
                    <input type="text" name="employee_code" id="employee_code" value="{{ old('employee_code') }}" required
                           class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition @error('employee_code') border-rose-500 @enderror">
                    @error('employee_code')
                        <span class="block text-rose-500 text-xs mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Joining Date -->
                <div>
                    <label for="joining_date" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Joining Date *</label>
                    <input type="date" name="joining_date" id="joining_date" value="{{ old('joining_date', today()->toDateString()) }}" required
                           class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 transition @error('joining_date') border-rose-500 @enderror">
                    @error('joining_date')
                        <span class="block text-rose-500 text-xs mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Exit Date -->
                <div>
                    <label for="exit_date" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Exit Date (Contract End)</label>
                    <input type="date" name="exit_date" id="exit_date" value="{{ old('exit_date') }}"
                           class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 transition @error('exit_date') border-rose-500 @enderror">
                    @error('exit_date')
                        <span class="block text-rose-500 text-xs mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Manager assignment -->
                <div>
                    <label for="manager_id" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Reporting Manager</label>
                    <select name="manager_id" id="manager_id"
                            class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 transition">
                        <option value="">No Reporting Manager</option>
                        @foreach($managers as $mng)
                            <option value="{{ $mng->id }}" {{ old('manager_id') == $mng->id ? 'selected' : '' }}>
                                {{ $mng->name }} ({{ $mng->roles->first()?->name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Location -->
                <div>
                    <label for="location_id" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Office Location *</label>
                    <select name="location_id" id="location_id" required
                            class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 transition">
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ old('location_id') == $loc->id ? 'selected' : '' }}>
                                {{ $loc->location_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Department -->
                <div>
                    <label for="department_id" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Department *</label>
                    <select name="department_id" id="department_id" required
                            class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 transition">
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->department_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Designation -->
                <div>
                    <label for="designation_id" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Designation *</label>
                    <select name="designation_id" id="designation_id" required
                            class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 transition">
                        @foreach($designations as $desg)
                            <option value="{{ $desg->id }}" {{ old('designation_id') == $desg->id ? 'selected' : '' }}>
                                {{ $desg->designation_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Shift -->
                <div>
                    <label for="shift_id" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Shift Schedule *</label>
                    <select name="shift_id" id="shift_id" required
                            class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 transition">
                        @foreach($shifts as $shf)
                            <option value="{{ $shf->id }}" {{ old('shift_id') == $shf->id ? 'selected' : '' }}>
                                {{ $shf->shift_name }} ({{ $shf->clock_in_time }} - {{ $shf->clock_out_time }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Section 3: Personal & Financial Information -->
        <div class="bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md space-y-4">
            <h3 class="text-lg font-bold text-indigo-400 border-b border-white/10 pb-2 mb-4">Personal Details & Bank Info</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Phone Number</label>
                    <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" placeholder="+1234567890"
                           oninput="this.value = this.value.replace(/[^0-9+\-\s()]/g, '');"
                           class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition @error('phone') border-rose-500 @enderror">
                    @error('phone')
                        <span class="block text-rose-500 text-xs mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- DOB -->
                <div>
                    <label for="dob" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Date of Birth</label>
                    <input type="date" name="dob" id="dob" value="{{ old('dob') }}"
                           class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 transition @error('dob') border-rose-500 @enderror">
                    @error('dob')
                        <span class="block text-rose-500 text-xs mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Gender -->
                <div>
                    <label for="gender" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Gender *</label>
                    <select name="gender" id="gender" required
                            class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 transition">
                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <!-- PAN/Tax ID -->
                <div>
                    <label for="pan_no" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">PAN/Tax ID Number</label>
                    <input type="text" name="pan_no" id="pan_no" value="{{ old('pan_no') }}" placeholder="PAN Number"
                           class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition @error('pan_no') border-rose-500 @enderror">
                    @error('pan_no')
                        <span class="block text-rose-500 text-xs mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Bank Name -->
                <div>
                    <label for="bank_name" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Bank Name</label>
                    <input type="text" name="bank_name" id="bank_name" value="{{ old('bank_name') }}" placeholder="e.g. Chase, HSBC"
                           class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition @error('bank_name') border-rose-500 @enderror">
                    @error('bank_name')
                        <span class="block text-rose-500 text-xs mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Bank Account Number -->
                <div>
                    <label for="bank_account_no" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Bank Account Number</label>
                    <input type="text" name="bank_account_no" id="bank_account_no" value="{{ old('bank_account_no') }}" placeholder="Account Number"
                           class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition @error('bank_account_no') border-rose-500 @enderror">
                    @error('bank_account_no')
                        <span class="block text-rose-500 text-xs mt-1">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end gap-4">
            <a href="{{ route('employees.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-850 text-slate-300 border border-slate-800 transition font-medium text-sm">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm transition shadow-lg shadow-indigo-500/25 cursor-pointer">
                Save Employee
            </button>
        </div>
    </form>
</div>

<script>
function togglePasswordVisibility(inputId, button) {
    const input = document.getElementById(inputId);
    const svg = button.querySelector('svg');
    if (input.type === 'password') {
        input.type = 'text';
        svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path>';
    } else {
        input.type = 'password';
        svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const joiningInput = document.getElementById('joining_date');
    const exitInput = document.getElementById('exit_date');
    
    function updateExitMin() {
        if (joiningInput.value) {
            exitInput.min = joiningInput.value;
        } else {
            exitInput.removeAttribute('min');
        }
    }
    
    joiningInput.addEventListener('change', updateExitMin);
    updateExitMin();
    
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        if (joiningInput.value && exitInput.value) {
            const joinDate = new Date(joiningInput.value);
            const exitDate = new Date(exitInput.value);
            if (exitDate < joinDate) {
                e.preventDefault();
                alert('Exit Date (Contract End) must be greater than or equal to Joining Date.');
            }
        }
    });
});
</script>
@endsection
