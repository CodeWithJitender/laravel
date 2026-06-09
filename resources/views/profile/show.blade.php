@extends('layouts.app')

@section('title', 'My Profile')
@section('page_title', 'My Profile Hub')

@section('content')
<div class="space-y-6 max-w-6xl mx-auto" x-data="{ 
    openContactModal: false, 
    editMode: false, 
    contactId: '',
    contactName: '',
    contactRelationship: '',
    contactPhone: '',
    contactEmail: '',
    contactIsPrimary: false
}">
    <!-- Alerts -->
    @if(session('success'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-2xl text-sm shadow-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-2xl text-sm shadow-sm">
            {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="p-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-2xl text-sm shadow-sm">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Profile Header Banner -->
    <div class="bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md flex flex-col md:flex-row items-center md:items-start gap-6 relative overflow-hidden">
        <div class="absolute top-[-20%] right-[-10%] w-[250px] h-[250px] rounded-full bg-indigo-500/10 blur-[60px] pointer-events-none"></div>

        <!-- Initial Avatar -->
        <div class="w-24 h-24 rounded-2xl bg-gradient-to-tr from-indigo-500 to-purple-600 flex items-center justify-center font-bold text-white text-3xl shadow-xl shadow-indigo-500/25 shrink-0 border border-white/10">
            {{ substr($employee->name, 0, 2) }}
        </div>

        <div class="flex-grow text-center md:text-left space-y-2">
            <h2 class="text-2xl font-bold text-white">{{ $employee->name }}</h2>
            <p class="text-slate-300 font-medium text-sm">
                {{ $employee->employeeDetail?->designation?->designation_name ?? 'Staff Associate' }} &bull; 
                <span class="text-slate-400">{{ $employee->employeeDetail?->department?->department_name ?? 'No Department' }}</span>
            </p>
            <div class="flex flex-wrap gap-x-4 gap-y-1 justify-center md:justify-start text-xs text-slate-400 pt-2">
                <span class="flex items-center gap-1">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    {{ $employee->email }}
                </span>
                @if($employee->employeeDetail?->phone)
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        {{ $employee->employeeDetail->phone }}
                    </span>
                @endif
                <span class="flex items-center gap-1">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Joined: {{ $employee->employeeDetail?->joining_date?->format('M d, Y') ?? 'N/A' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Sidebar Navigation Tabs -->
        <div class="lg:col-span-1 bg-white/5 border border-white/10 rounded-2xl p-4 backdrop-blur-md h-fit space-y-1">
            <button id="btn-profile" onclick="switchTab(event, 'tab-profile')" class="tab-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm text-left transition duration-150 bg-indigo-600/10 text-indigo-400 border border-indigo-500/10 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                Personal Overview
            </button>
            <button id="btn-employment" onclick="switchTab(event, 'tab-employment')" class="tab-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm text-left transition duration-150 text-slate-400 hover:text-slate-200 hover:bg-white/5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                Employment Specs
            </button>
            <button id="btn-emergency" onclick="switchTab(event, 'tab-emergency')" class="tab-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm text-left transition duration-150 text-slate-400 hover:text-slate-200 hover:bg-white/5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                </svg>
                Emergency Contacts
            </button>
            <button id="btn-documents" onclick="switchTab(event, 'tab-documents')" class="tab-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm text-left transition duration-150 text-slate-400 hover:text-slate-200 hover:bg-white/5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Document Vault
            </button>
            <button id="btn-security" onclick="switchTab(event, 'tab-security')" class="tab-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm text-left transition duration-150 text-slate-400 hover:text-slate-200 hover:bg-white/5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                Security & Locks
            </button>
        </div>

        <!-- Content Panel -->
        <div class="lg:col-span-3 space-y-6">
            <!-- TAB: Personal Overview -->
            <div id="tab-profile" class="tab-content bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md space-y-6">
                <h3 class="text-sm font-bold uppercase tracking-wider text-indigo-400 border-b border-white/5 pb-3">Personal & Demographics Settings</h3>
                
                <form action="{{ route('profile.update') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Contact Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $employee->employeeDetail?->phone) }}" class="w-full bg-slate-900 border border-white/10 rounded-xl px-3.5 py-2.5 text-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Gender</label>
                        <select name="gender" required class="w-full bg-slate-900 border border-white/10 rounded-xl px-3.5 py-2.5 text-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none select-dark">
                            <option value="male" {{ old('gender', $employee->employeeDetail?->gender) === 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender', $employee->employeeDetail?->gender) === 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ old('gender', $employee->employeeDetail?->gender) === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Date of Birth</label>
                        <input type="date" name="dob" value="{{ old('dob', $employee->employeeDetail?->dob?->toDateString()) }}" class="w-full bg-slate-900 border border-white/10 rounded-xl px-3.5 py-2.5 text-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Bank Name</label>
                        <input type="text" name="bank_name" value="{{ old('bank_name', $employee->employeeDetail?->bank_name) }}" class="w-full bg-slate-900 border border-white/10 rounded-xl px-3.5 py-2.5 text-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Bank Account Number</label>
                        <input type="text" name="bank_account_no" value="{{ old('bank_account_no', $employee->employeeDetail?->bank_account_no) }}" class="w-full bg-slate-900 border border-white/10 rounded-xl px-3.5 py-2.5 text-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none" placeholder="Masked/Encrypted in database">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">PAN Number (Tax registration)</label>
                        <input type="text" name="pan_no" value="{{ old('pan_no', $employee->employeeDetail?->pan_no) }}" class="w-full bg-slate-900 border border-white/10 rounded-xl px-3.5 py-2.5 text-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>

                    <div class="col-span-full pt-4 border-t border-white/5 flex justify-end">
                        <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-semibold shadow-lg shadow-indigo-500/20 transition cursor-pointer">
                            Save Profile Metrics
                        </button>
                    </div>
                </form>
            </div>

            <!-- TAB: Employment Specs (Read Only) -->
            <div id="tab-employment" class="tab-content bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md space-y-6 hidden">
                <h3 class="text-sm font-bold uppercase tracking-wider text-indigo-400 border-b border-white/5 pb-3">Employment Details</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                    <div>
                        <span class="block text-slate-500 font-medium text-xs uppercase tracking-wider">Employee Code</span>
                        <span class="block font-bold text-slate-200 mt-1 font-mono text-base">{{ $employee->employeeDetail?->employee_code ?? 'N/A' }}</span>
                    </div>

                    <div>
                        <span class="block text-slate-500 font-medium text-xs uppercase tracking-wider">Reporting Manager</span>
                        <span class="block font-semibold text-slate-200 mt-1">
                            {{ $employee->employeeDetail?->manager ? $employee->employeeDetail->manager->name : 'Direct Line' }}
                        </span>
                    </div>

                    <div>
                        <span class="block text-slate-500 font-medium text-xs uppercase tracking-wider">Department</span>
                        <span class="block font-semibold text-slate-200 mt-1">{{ $employee->employeeDetail?->department?->department_name ?? 'N/A' }}</span>
                    </div>

                    <div>
                        <span class="block text-slate-500 font-medium text-xs uppercase tracking-wider">Designation</span>
                        <span class="block font-semibold text-slate-200 mt-1">{{ $employee->employeeDetail?->designation?->designation_name ?? 'N/A' }}</span>
                    </div>

                    <div>
                        <span class="block text-slate-500 font-medium text-xs uppercase tracking-wider">Assigned Shift</span>
                        <span class="block font-semibold text-slate-200 mt-1">
                            {{ $employee->employeeDetail?->shift ? $employee->employeeDetail->shift->shift_name : 'No Shift' }} 
                            ({{ $employee->employeeDetail?->shift?->clock_in_time }} &rarr; {{ $employee->employeeDetail?->shift?->clock_out_time }})
                        </span>
                    </div>

                    <div>
                        <span class="block text-slate-500 font-medium text-xs uppercase tracking-wider">Office Location</span>
                        <span class="block font-semibold text-slate-200 mt-1">{{ $employee->employeeDetail?->location?->location_name ?? 'N/A' }}</span>
                    </div>

                    <div>
                        <span class="block text-slate-500 font-medium text-xs uppercase tracking-wider">Joining Date</span>
                        <span class="block font-semibold text-slate-200 mt-1">{{ $employee->employeeDetail?->joining_date?->format('F d, Y') ?? 'N/A' }}</span>
                    </div>
                </div>

                <div class="p-4 bg-slate-900/50 rounded-xl border border-white/5 mt-4">
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Note: Core employment values are managed by HR/Admins. To request adjustments to designations, shifts, or departments, please open a correction ticket or contact HR desk.
                    </p>
                </div>
            </div>

            <!-- TAB: Emergency Contacts -->
            <div id="tab-emergency" class="tab-content bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md space-y-6 hidden">
                <div class="flex justify-between items-center border-b border-white/5 pb-3">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-indigo-400">Emergency Contacts Registry</h3>
                    <button 
                        @click="
                            editMode = false;
                            contactId = '';
                            contactName = '';
                            contactRelationship = '';
                            contactPhone = '';
                            contactEmail = '';
                            contactIsPrimary = false;
                            openContactModal = true;
                        "
                        class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-xs font-semibold shadow transition cursor-pointer flex items-center gap-1.5"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Contact
                    </button>
                </div>

                <div class="divide-y divide-white/5">
                    @forelse($employee->employeeDetail?->emergencyContacts ?? [] as $contact)
                        <div class="py-4 flex items-center justify-between text-sm">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-white">{{ $contact->name }}</span>
                                    <span class="px-2 py-0.5 text-[9px] rounded-md border border-slate-700 bg-slate-800 text-slate-400 font-semibold">{{ $contact->relationship }}</span>
                                    @if($contact->is_primary)
                                        <span class="px-2 py-0.5 text-[9px] rounded-md border border-emerald-500/20 bg-emerald-500/10 text-emerald-400 font-bold uppercase tracking-wide">Primary</span>
                                    @endif
                                </div>
                                <div class="text-xs text-slate-400 space-y-0.5 font-mono">
                                    <div>Phone: {{ $contact->phone }}</div>
                                    @if($contact->email)
                                        <div>Email: {{ $contact->email }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <button 
                                    @click="
                                        editMode = true;
                                        contactId = '{{ $contact->id }}';
                                        contactName = '{{ $contact->name }}';
                                        contactRelationship = '{{ $contact->relationship }}';
                                        contactPhone = '{{ $contact->phone }}';
                                        contactEmail = '{{ $contact->email }}';
                                        contactIsPrimary = {{ $contact->is_primary ? 'true' : 'false' }};
                                        openContactModal = true;
                                    "
                                    class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-lg text-xs font-semibold text-slate-300 transition cursor-pointer"
                                >
                                    Edit
                                </button>
                                <form action="/profile/emergency-contacts/{{ $contact->id }}" method="POST" onsubmit="return confirm('Remove this contact?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 bg-slate-800 hover:bg-rose-950 hover:text-rose-400 border border-slate-700 rounded-lg text-xs font-semibold text-slate-400 transition cursor-pointer">
                                        Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-slate-500 text-sm">
                            No emergency contacts declared. Please list at least one primary contact.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- TAB: Document Vault -->
            <div id="tab-documents" class="tab-content bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md space-y-6 hidden">
                <div class="flex justify-between items-center border-b border-white/5 pb-3">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-indigo-400">Document Vault</h3>
                    <span class="text-xs text-slate-400">{{ $employee->documents->count() }} Files Stored</span>
                </div>
                
                <div class="divide-y divide-white/5">
                    @forelse($employee->documents as $doc)
                        <div class="py-3.5 flex items-center justify-between text-sm">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center text-indigo-400 shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <span class="block font-semibold text-white">{{ $doc->file_name }}</span>
                                    <span class="block text-[10px] text-slate-500 mt-0.5">{{ number_format($doc->file_size / 1024, 1) }} KB &bull; Type: {{ $doc->document_type }}</span>
                                </div>
                            </div>
                            <a href="{{ Storage::url($doc->file_path) }}" target="_blank" download class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 border border-white/5 text-slate-300 font-medium text-xs transition cursor-pointer">
                                Download File
                            </a>
                        </div>
                    @empty
                        <div class="py-8 text-center text-slate-500 text-sm">
                            No credentials or documents loaded. HR managers must allocate stored paperwork.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- TAB: Security Details -->
            <div id="tab-security" class="tab-content bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md space-y-6 hidden">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Change Password Form -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-indigo-400 border-b border-white/5 pb-3">Update Account Password</h3>
                        
                        <form action="/change-password" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Current Password</label>
                                <input type="password" name="current_password" required class="w-full bg-slate-900 border border-white/10 rounded-xl px-3.5 py-2.5 text-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">New Password</label>
                                <input type="password" name="new_password" required class="w-full bg-slate-900 border border-white/10 rounded-xl px-3.5 py-2.5 text-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Confirm New Password</label>
                                <input type="password" name="new_password_confirmation" required class="w-full bg-slate-900 border border-white/10 rounded-xl px-3.5 py-2.5 text-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            </div>

                            <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-semibold shadow transition cursor-pointer">
                                Update Password
                            </button>
                        </form>
                    </div>

                    <!-- Active Device Sessions list -->
                    <div class="space-y-4">
                        <div class="flex justify-between items-center border-b border-white/5 pb-3">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-indigo-400">Active Device Logs</h3>
                            <form action="{{ route('sessions.clear_all') }}" method="POST" onsubmit="return confirm('Terminate all other device logins?')">
                                @csrf
                                <button type="submit" class="text-xs text-rose-400 hover:underline font-semibold cursor-pointer">
                                    Clear Others
                                </button>
                            </form>
                        </div>

                        <div class="divide-y divide-white/5">
                            @foreach($sessions as $session)
                                <div class="py-3 flex items-center justify-between text-xs">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-slate-200">{{ $session['browser'] }}</span>
                                            <span class="text-slate-500">on</span>
                                            <span class="font-bold text-indigo-400">{{ $session['platform'] }}</span>
                                            @if($session['is_current'])
                                                <span class="px-1.5 py-0.2 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[9px] rounded font-bold uppercase">This Device</span>
                                            @endif
                                        </div>
                                        <div class="text-[10px] text-slate-500 mt-1 font-mono">
                                            IP: {{ $session['ip_address'] }} &bull; Active: {{ $session['last_active'] }}
                                        </div>
                                    </div>
                                    @if(!$session['is_current'])
                                        <form action="{{ route('sessions.destroy', $session['id']) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 text-slate-500 hover:text-rose-400 transition cursor-pointer" title="Terminate Session">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Modal -->
    <div 
        x-show="openContactModal" 
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm p-4"
        x-transition
        style="display: none;"
    >
        <div 
            @click.outside="openContactModal = false" 
            class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl relative"
        >
            <div class="flex items-center justify-between pb-4 border-b border-slate-800 mb-4">
                <h3 class="text-base font-bold text-slate-200" x-text="editMode ? 'Edit Emergency Contact' : 'Add Emergency Contact'"></h3>
                <button @click="openContactModal = false" class="text-slate-400 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form :action="editMode ? '/profile/emergency-contacts/' + contactId : '{{ route('profile.emergency-contacts.store') }}'" method="POST" class="space-y-4">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Name</label>
                    <input type="text" name="name" x-model="contactName" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Relationship</label>
                    <input type="text" name="relationship" x-model="contactRelationship" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 transition" placeholder="e.g. Spouse, Father, Friend">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Phone Number</label>
                    <input type="text" name="phone" x-model="contactPhone" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Email Address (Optional)</label>
                    <input type="email" name="email" x-model="contactEmail" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 transition">
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" name="is_primary" id="is_primary" value="1" :checked="contactIsPrimary" class="rounded border-slate-700 text-indigo-600 focus:ring-indigo-500 bg-slate-800">
                    <label for="is_primary" class="text-xs font-semibold text-slate-300">Set as Primary Emergency Contact</label>
                </div>

                <div class="pt-4 border-t border-slate-800 flex justify-end gap-3">
                    <button type="button" @click="openContactModal = false" class="px-4 py-2 bg-slate-850 hover:bg-slate-800 border border-slate-850 rounded-xl text-slate-400 text-xs transition cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-semibold shadow-lg shadow-indigo-500/20 transition cursor-pointer">
                        Save Contact
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function switchTab(evt, tabId) {
        // Hide all tabs
        const tabContents = document.getElementsByClassName("tab-content");
        for (let i = 0; i < tabContents.length; i++) {
            tabContents[i].classList.add("hidden");
        }

        // Remove active styling from buttons
        const tabBtns = document.getElementsByClassName("tab-btn");
        for (let i = 0; i < tabBtns.length; i++) {
            tabBtns[i].classList.remove("bg-indigo-600/10", "text-indigo-400", "border", "border-indigo-500/10");
            tabBtns[i].classList.add("text-slate-400", "hover:text-slate-200", "hover:bg-white/5");
        }

        // Show active tab
        document.getElementById(tabId).classList.remove("hidden");

        // Set button active
        evt.currentTarget.classList.remove("text-slate-400", "hover:text-slate-200", "hover:bg-white/5");
        evt.currentTarget.classList.add("bg-indigo-600/10", "text-indigo-400", "border", "border-indigo-500/10");
    }

    document.addEventListener("DOMContentLoaded", function() {
        // Automatically switch tabs if present in query param
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('tab');
        if (activeTab === 'emergency') {
            document.getElementById('btn-emergency').click();
        } else if (activeTab === 'security') {
            document.getElementById('btn-security').click();
        } else if (activeTab === 'documents') {
            document.getElementById('btn-documents').click();
        } else if (activeTab === 'employment') {
            document.getElementById('btn-employment').click();
        }
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endsection
