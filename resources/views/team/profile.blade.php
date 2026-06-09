@extends('layouts.app')

@section('title', $employee->name)
@section('page_title', 'Team Member Profile')

@section('content')
<div class="space-y-6 max-w-6xl mx-auto">
    <!-- Header Summary Card -->
    <div class="bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md flex flex-col md:flex-row items-center md:items-start gap-6 relative overflow-hidden">
        <div class="absolute top-[-20%] right-[-10%] w-[250px] h-[250px] rounded-full bg-indigo-500/10 blur-[60px] pointer-events-none"></div>

        <!-- Avatar / Initial -->
        <div class="w-24 h-24 rounded-2xl bg-gradient-to-tr from-indigo-500 to-purple-600 flex items-center justify-center font-bold text-white text-3xl shadow-xl shadow-indigo-500/25 shrink-0 border border-white/10">
            {{ substr($employee->name, 0, 2) }}
        </div>

        <!-- Details -->
        <div class="flex-grow text-center md:text-left space-y-2">
            <div class="flex flex-col md:flex-row md:items-center gap-2 justify-center md:justify-start">
                <h2 class="text-2xl font-bold text-white">{{ $employee->name }}</h2>
                <div class="flex items-center gap-2 justify-center">
                    <span class="px-2.5 py-0.5 text-xs font-semibold text-indigo-400 bg-indigo-500/10 border border-indigo-500/20 rounded-md">
                        {{ $employee->roles->first()?->name ?? 'None' }}
                    </span>
                    @if($employee->status === 'active')
                        <span class="px-2.5 py-0.5 text-xs font-semibold text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 rounded-md uppercase">
                            Active
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 text-xs font-semibold text-slate-400 bg-slate-500/10 border border-slate-500/20 rounded-md uppercase">
                            Inactive
                        </span>
                    @endif
                </div>
            </div>
            
            <p class="text-slate-300 font-medium text-sm">
                {{ $employee->employeeDetail?->designation?->designation_name ?? 'No Designation' }} &bull; 
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

        <!-- Back to Directory -->
        <div class="flex items-center gap-2 self-center shrink-0">
            <a href="/team-members" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 rounded-xl text-xs font-semibold transition cursor-pointer">
                Back to Team Directory
            </a>
        </div>
    </div>

    <!-- Main Section Tabs -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Sidebar Navigation Tabs -->
        <div class="lg:col-span-1 bg-white/5 border border-white/10 rounded-2xl p-4 backdrop-blur-md h-fit space-y-1">
            <button onclick="switchTab(event, 'tab-profile')" class="tab-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm text-left transition duration-150 bg-indigo-600/10 text-indigo-400 border border-indigo-500/10 cursor-pointer">
                Profile Details
            </button>
            <button onclick="switchTab(event, 'tab-emergency')" class="tab-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm text-left transition duration-150 text-slate-400 hover:text-slate-200 hover:bg-white/5 cursor-pointer">
                Emergency Contacts
            </button>
            <button onclick="switchTab(event, 'tab-documents')" class="tab-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm text-left transition duration-150 text-slate-400 hover:text-slate-200 hover:bg-white/5 cursor-pointer">
                Documents Vault
            </button>
            <button onclick="switchTab(event, 'tab-activity')" class="tab-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-sm text-left transition duration-150 text-slate-400 hover:text-slate-200 hover:bg-white/5 cursor-pointer">
                Activity Timeline
            </button>
        </div>

        <!-- Content Panel -->
        <div class="lg:col-span-3 space-y-6">
            <!-- TAB: Profile Details -->
            <div id="tab-profile" class="tab-content bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md space-y-6">
                <!-- Group 1: Employment Details -->
                <div class="space-y-4">
                    <h4 class="text-sm font-bold text-indigo-400 uppercase tracking-widest border-b border-white/5 pb-2">Employment Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="block text-slate-500 font-medium">Employee Code</span>
                            <span class="block font-semibold text-white mt-1">{{ $employee->employeeDetail?->employee_code ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="block text-slate-500 font-medium">Reporting Manager</span>
                            <span class="block font-semibold text-white mt-1">
                                {{ $employee->employeeDetail?->manager ? $employee->employeeDetail->manager->name : 'No Manager' }}
                            </span>
                        </div>
                        <div>
                            <span class="block text-slate-500 font-medium">Work Shift</span>
                            <span class="block font-semibold text-white mt-1">
                                {{ $employee->employeeDetail?->shift ? $employee->employeeDetail->shift->shift_name : 'N/A' }}
                            </span>
                        </div>
                        <div>
                            <span class="block text-slate-500 font-medium">Office Location</span>
                            <span class="block font-semibold text-white mt-1">{{ $employee->employeeDetail?->location?->location_name ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="block text-slate-500 font-medium">Department</span>
                            <span class="block font-semibold text-white mt-1">{{ $employee->employeeDetail?->department?->department_name ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="block text-slate-500 font-medium">Designation</span>
                            <span class="block font-semibold text-white mt-1">{{ $employee->employeeDetail?->designation?->designation_name ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Group 2: Personal Details -->
                <div class="space-y-4 pt-4 border-t border-white/5">
                    <h4 class="text-sm font-bold text-indigo-400 uppercase tracking-widest border-b border-white/5 pb-2">Personal Demographics</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="block text-slate-500 font-medium">Gender</span>
                            <span class="block font-semibold text-white mt-1 text-capitalize">{{ $employee->employeeDetail?->gender ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="block text-slate-500 font-medium">Date of Birth</span>
                            <span class="block font-semibold text-white mt-1">
                                {{ $employee->employeeDetail?->dob ? $employee->employeeDetail->dob->format('M d, Y') : 'N/A' }}
                            </span>
                        </div>
                        <div>
                            <span class="block text-slate-500 font-medium">Contact Phone</span>
                            <span class="block font-semibold text-white mt-1">{{ $employee->employeeDetail?->phone ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB: Emergency Contacts -->
            <div id="tab-emergency" class="tab-content bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md space-y-6 hidden">
                <h4 class="text-sm font-bold text-indigo-400 uppercase tracking-widest border-b border-white/5 pb-2">Emergency Contacts</h4>
                
                <div class="divide-y divide-white/5">
                    @forelse($employee->employeeDetail?->emergencyContacts ?? [] as $contact)
                        <div class="py-3 flex items-center justify-between text-sm">
                            <div>
                                <span class="block font-semibold text-white">{{ $contact->contact_name }}</span>
                                <span class="block text-xs text-slate-400 mt-0.5">{{ $contact->relationship }}</span>
                            </div>
                            <span class="text-slate-300 font-medium">{{ $contact->phone }}</span>
                        </div>
                    @empty
                        <div class="py-8 text-center text-slate-500 text-sm">
                            No emergency contacts registered.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- TAB: Documents -->
            <div id="tab-documents" class="tab-content bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md space-y-6 hidden">
                <div class="flex justify-between items-center border-b border-white/5 pb-3">
                    <h4 class="text-sm font-bold text-indigo-400 uppercase tracking-widest">Document Vault</h4>
                    <span class="text-xs text-slate-400">{{ $employee->documents->count() }} Files Stored</span>
                </div>
                
                <div class="divide-y divide-white/5">
                    @forelse($employee->documents as $doc)
                        <div class="py-3 flex items-center justify-between text-sm">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center text-indigo-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <span class="block font-semibold text-white">{{ $doc->document_name }}</span>
                                    <span class="block text-xs text-slate-500 mt-0.5">{{ number_format($doc->file_size / 1024, 1) }} KB &bull; Uploaded {{ $doc->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-slate-500 text-sm">
                            No files uploaded.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- TAB: Activity Timeline -->
            <div id="tab-activity" class="tab-content bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md space-y-6 hidden">
                <h4 class="text-sm font-bold text-indigo-400 uppercase tracking-widest border-b border-white/5 pb-2">Audit Logs & History</h4>
                
                <div class="flow-root">
                    <ul role="list" class="-mb-8">
                        @forelse($activities as $act)
                            <li>
                                <div class="relative pb-8">
                                    @if(!$loop->last)
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-slate-800" aria-hidden="true"></span>
                                    @endif
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center ring-8 ring-slate-950 text-indigo-400">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="flex-grow pt-1.5 flex justify-between space-x-4 text-sm">
                                            <div>
                                                <span class="text-slate-300 font-semibold">{{ $act->activity }}</span>
                                                <span class="text-slate-400 block mt-0.5 text-xs">{{ $act->description }}</span>
                                            </div>
                                            <div class="text-right text-xs text-slate-500 whitespace-nowrap">
                                                <span>{{ $act->created_at->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <div class="py-8 text-center text-slate-500 text-sm">
                                No activity recorded.
                            </div>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function switchTab(evt, tabId) {
        const tabContents = document.getElementsByClassName("tab-content");
        for (let i = 0; i < tabContents.length; i++) {
            tabContents[i].classList.add("hidden");
        }

        const tabBtns = document.getElementsByClassName("tab-btn");
        for (let i = 0; i < tabBtns.length; i++) {
            tabBtns[i].classList.remove("bg-indigo-600/10", "text-indigo-400", "border", "border-indigo-500/10");
            tabBtns[i].classList.add("text-slate-400", "hover:text-slate-200", "hover:bg-white/5");
        }

        document.getElementById(tabId).classList.remove("hidden");
        evt.currentTarget.classList.remove("text-slate-400", "hover:text-slate-200", "hover:bg-white/5");
        evt.currentTarget.classList.add("bg-indigo-600/10", "text-indigo-400", "border", "border-indigo-500/10");
    }
</script>
@endsection
