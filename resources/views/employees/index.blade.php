@extends('layouts.app')

@section('title', 'Employees')
@section('page_title', 'Employee Directory')

@section('content')
<div class="space-y-6">
    @if(session('import_errors'))
        <div class="bg-rose-500/10 border border-rose-500/20 text-rose-200 rounded-2xl p-6 space-y-3">
            <div class="flex items-center gap-2 font-bold text-rose-400">
                <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <span>Some rows had validation errors and were skipped:</span>
            </div>
            <ul class="list-disc pl-5 space-y-1 text-sm text-slate-300 max-h-60 overflow-y-auto">
                @foreach(session('import_errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <!-- Top Action Banner -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md">
        <div>
            <h2 class="text-xl font-bold text-white">Manage Employees</h2>
            <p class="text-slate-400 text-sm mt-1">View, add, update, and search active or inactive employee files.</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="document.getElementById('importModal').classList.remove('hidden')" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-medium text-sm flex items-center gap-2 transition duration-200 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                </svg>
                Import Employees
            </button>
            <a href="{{ route('employees.create') }}" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm flex items-center gap-2 transition duration-200 shadow-lg shadow-indigo-500/25 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add Employee
            </a>
        </div>
    </div>

    <!-- Filters Panel -->
    <div class="bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md">
        <form action="{{ route('employees.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div>
                <label for="search" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Search</label>
                <div class="relative">
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Code, name, email..." 
                           class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition duration-200">
                </div>
            </div>

            <!-- Department Filter -->
            <div>
                <label for="department_id" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Department</label>
                <select name="department_id" id="department_id" 
                        class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 transition duration-200">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->department_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Location Filter -->
            <div>
                <label for="location_id" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Location</label>
                <select name="location_id" id="location_id" 
                        class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 transition duration-200">
                    <option value="">All Locations</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>
                            {{ $loc->location_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div class="flex items-end gap-2">
                <div class="flex-grow">
                    <label for="status" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Status</label>
                    <select name="status" id="status" 
                            class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 transition duration-200">
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Statuses</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-medium text-sm transition duration-200 border border-slate-700 cursor-pointer">
                    Apply
                </button>
            </div>
        </form>
    </div>

    <!-- Table List -->
    <div class="bg-white/5 border border-white/10 rounded-2xl overflow-hidden backdrop-blur-md">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-white/10 bg-white/[0.02]">
                        <th class="px-6 py-4 w-10"><input type="checkbox" id="select-all" class="w-4 h-4 rounded border-slate-700 text-indigo-600 focus:ring-indigo-500 bg-slate-900 cursor-pointer"></th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Employee</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Code</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Department</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Designation</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Location</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($employees as $emp)
                        <tr class="hover:bg-white/[0.02] transition duration-150" data-id="{{ $emp->id }}">
                            <td class="px-6 py-4 w-10">
                                <input type="checkbox" class="row-checkbox w-4 h-4 rounded border-slate-700 text-indigo-600 focus:ring-indigo-500 bg-slate-900 cursor-pointer" value="{{ $emp->id }}">
                            </td>
                            <!-- Name / Email -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-indigo-600/10 border border-indigo-500/20 flex items-center justify-center font-bold text-indigo-400 text-sm">
                                        {{ substr($emp->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <span class="block font-semibold text-white">{{ $emp->name }}</span>
                                        <span class="block text-xs text-slate-400 mt-0.5">{{ $emp->email }}</span>
                                    </div>
                                </div>
                            </td>
                            
                            <!-- Employee Code -->
                            <td class="px-6 py-4 text-sm font-semibold text-slate-200">
                                {{ $emp->employeeDetail?->employee_code ?? 'N/A' }}
                            </td>
                            
                            <!-- Department -->
                            <td class="px-6 py-4 text-sm text-slate-300">
                                {{ $emp->employeeDetail?->department?->department_name ?? 'N/A' }}
                            </td>
                            
                            <!-- Designation -->
                            <td class="px-6 py-4 text-sm text-slate-300">
                                {{ $emp->employeeDetail?->designation?->designation_name ?? 'N/A' }}
                            </td>
                            
                            <!-- Location -->
                            <td class="px-6 py-4 text-sm text-slate-300">
                                {{ $emp->employeeDetail?->location?->location_name ?? 'N/A' }}
                            </td>

                            <!-- Role -->
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-[10px] font-bold text-indigo-400 bg-indigo-500/10 border border-indigo-500/20 rounded-lg">
                                    {{ $emp->roles->first()?->name ?? 'None' }}
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4">
                                @if($emp->status === 'active')
                                    <span class="px-2.5 py-1 text-[10px] font-bold text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 rounded-lg uppercase">
                                        Active
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 text-[10px] font-bold text-slate-400 bg-slate-500/10 border border-slate-500/20 rounded-lg uppercase">
                                        Inactive
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- View Details -->
                                    <a href="{{ route('employees.show', $emp->id) }}" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-lg border border-slate-700 transition" title="View details">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>

                                    <!-- Edit -->
                                    <a href="{{ route('employees.edit', $emp->id) }}" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-lg border border-slate-700 transition" title="Edit employee">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>

                                    <!-- Delete -->
                                    @if($emp->id !== auth()->id())
                                        <form action="{{ route('employees.destroy', $emp->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this employee?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 hover:text-rose-300 rounded-lg border border-rose-500/20 transition cursor-pointer" title="Delete employee">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-8 text-center text-slate-500">
                                No employees found matching the filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($employees->hasPages())
            <div class="px-6 py-4 border-t border-white/10 bg-white/[0.01]">
                {{ $employees->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Import Modal -->
<div id="importModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" onclick="document.getElementById('importModal').classList.add('hidden')"></div>
    <div class="flex min-h-full items-start justify-center p-4 md:py-10">
        <div class="bg-slate-900 border border-white/10 rounded-2xl p-6 max-w-2xl w-full relative z-10 shadow-2xl space-y-6">
            <div class="flex items-center justify-between border-b border-white/5 pb-4">
                <h3 class="text-lg font-bold text-white">Import Employees via CSV</h3>
                <button onclick="document.getElementById('importModal').classList.add('hidden')" class="p-1.5 hover:bg-slate-800 rounded-lg text-slate-400 hover:text-white transition cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form action="{{ route('employees.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                
                <div class="space-y-2">
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Expected Excel/CSV Column Format</label>
                    <div class="overflow-x-auto border border-white/10 rounded-xl bg-slate-950/40 max-w-full">
                        <table class="min-w-full divide-y divide-white/5 text-[10px] text-left text-slate-300">
                            <thead class="bg-white/5 text-slate-400 font-bold uppercase">
                                <tr>
                                    <th class="px-3 py-2 whitespace-nowrap border-r border-white/5">Name *</th>
                                    <th class="px-3 py-2 whitespace-nowrap border-r border-white/5">Email *</th>
                                    <th class="px-3 py-2 whitespace-nowrap border-r border-white/5">Password</th>
                                    <th class="px-3 py-2 whitespace-nowrap border-r border-white/5">Role *</th>
                                    <th class="px-3 py-2 whitespace-nowrap border-r border-white/5">Status</th>
                                    <th class="px-3 py-2 whitespace-nowrap border-r border-white/5">Employee Code *</th>
                                    <th class="px-3 py-2 whitespace-nowrap border-r border-white/5">Joining Date *</th>
                                    <th class="px-3 py-2 whitespace-nowrap border-r border-white/5">Gender *</th>
                                    <th class="px-3 py-2 whitespace-nowrap border-r border-white/5">Phone</th>
                                    <th class="px-3 py-2 whitespace-nowrap border-r border-white/5">Date of Birth</th>
                                    <th class="px-3 py-2 whitespace-nowrap border-r border-white/5">Department *</th>
                                    <th class="px-3 py-2 whitespace-nowrap border-r border-white/5">Designation *</th>
                                    <th class="px-3 py-2 whitespace-nowrap border-r border-white/5">Location *</th>
                                    <th class="px-3 py-2 whitespace-nowrap border-r border-white/5">Shift *</th>
                                    <th class="px-3 py-2 whitespace-nowrap border-r border-white/5">Manager Email</th>
                                    <th class="px-3 py-2 whitespace-nowrap border-r border-white/5">Bank Name</th>
                                    <th class="px-3 py-2 whitespace-nowrap border-r border-white/5">Bank Account No</th>
                                    <th class="px-3 py-2 whitespace-nowrap">PAN No</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5 text-slate-400 font-mono">
                                <tr class="hover:bg-white/[0.02]">
                                    <td class="px-3 py-1.5 whitespace-nowrap border-r border-white/5">John Doe</td>
                                    <td class="px-3 py-1.5 whitespace-nowrap border-r border-white/5">john@example.com</td>
                                    <td class="px-3 py-1.5 whitespace-nowrap border-r border-white/5">Welcome@123</td>
                                    <td class="px-3 py-1.5 whitespace-nowrap border-r border-white/5">Employee</td>
                                    <td class="px-3 py-1.5 whitespace-nowrap border-r border-white/5">active</td>
                                    <td class="px-3 py-1.5 whitespace-nowrap border-r border-white/5">EMP-999</td>
                                    <td class="px-3 py-1.5 whitespace-nowrap border-r border-white/5">2026-06-12</td>
                                    <td class="px-3 py-1.5 whitespace-nowrap border-r border-white/5">male</td>
                                    <td class="px-3 py-1.5 whitespace-nowrap border-r border-white/5">1234567890</td>
                                    <td class="px-3 py-1.5 whitespace-nowrap border-r border-white/5">1995-05-15</td>
                                    <td class="px-3 py-1.5 whitespace-nowrap border-r border-white/5">Engineering</td>
                                    <td class="px-3 py-1.5 whitespace-nowrap border-r border-white/5">Software Engineer</td>
                                    <td class="px-3 py-1.5 whitespace-nowrap border-r border-white/5">Headquarters</td>
                                    <td class="px-3 py-1.5 whitespace-nowrap border-r border-white/5">Regular Shift</td>
                                    <td class="px-3 py-1.5 whitespace-nowrap border-r border-white/5">manager@company.com</td>
                                    <td class="px-3 py-1.5 whitespace-nowrap border-r border-white/5">Chase Bank</td>
                                    <td class="px-3 py-1.5 whitespace-nowrap border-r border-white/5">123456789</td>
                                    <td class="px-3 py-1.5 whitespace-nowrap">ABCDE1234F</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1">* Required fields. You can scroll horizontally to see all columns.</p>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Step 1: Download Template</label>
                    <p class="text-xs text-slate-400">Download the pre-structured Excel/CSV template prefilled with some valid database values to guide your input.</p>
                    <a href="{{ route('employees.import.template') }}" class="inline-flex items-center gap-2 text-xs font-bold text-indigo-400 hover:text-indigo-300 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Download CSV Template
                    </a>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Step 2: Upload Completed CSV File</label>
                    <div class="border-2 border-dashed border-slate-700 hover:border-indigo-500 rounded-xl p-6 text-center cursor-pointer transition relative group">
                        <input type="file" name="file" accept=".csv" required class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">
                        <svg class="w-8 h-8 text-slate-500 group-hover:text-indigo-400 mx-auto mb-2 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        <span class="block text-xs text-slate-300 font-semibold group-hover:text-indigo-300">Choose CSV File or drag it here</span>
                        <span class="block text-[10px] text-slate-500 mt-1">Accepts .csv files up to 4MB</span>
                    </div>
                </div>

                <div class="bg-slate-950/40 rounded-xl p-4 border border-white/5 space-y-2">
                    <span class="block text-xs font-bold text-slate-300">Tips for success:</span>
                    <ul class="list-disc pl-4 text-[10px] text-slate-400 space-y-1">
                        <li>Make sure Department, Designation, Location, and Shift names match exactly what is in the system.</li>
                        <li>Emails and Employee Codes must be unique.</li>
                        <li>Date formats should be YYYY-MM-DD (e.g. 2026-06-12).</li>
                        <li>Password is optional; if left empty, it will default to <strong>Welcome@123</strong>.</li>
                        <li>Supported Spatie Roles: Admin, Manager, Employee.</li>
                    </ul>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-white/5 pt-4">
                    <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-medium text-xs transition cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-xs transition cursor-pointer">
                        Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Floating Action Bar -->
<div id="bulk-action-bar" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-40 bg-slate-900/90 border border-indigo-500/25 rounded-2xl py-3 px-6 shadow-2xl backdrop-blur-md flex items-center gap-6 transition-all duration-300 translate-y-24 opacity-0">
    <div class="flex items-center gap-2">
        <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 animate-pulse"></span>
        <span class="text-xs font-semibold text-slate-200" id="selected-count">0 selected</span>
    </div>
    <div class="h-4 w-px bg-white/10"></div>
    <div class="flex items-center gap-2">
        <button onclick="openBulkEditModal()" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl text-xs transition cursor-pointer">
            Bulk Edit
        </button>
        <button onclick="confirmBulkDelete()" class="px-3 py-1.5 bg-rose-600/10 hover:bg-rose-600/20 text-rose-400 hover:text-rose-300 rounded-xl text-xs font-semibold border border-rose-500/20 transition cursor-pointer">
            Bulk Delete
        </button>
    </div>
</div>

<!-- Bulk Edit Modal -->
<div id="bulkEditModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm" onclick="closeBulkEditModal()"></div>
    <div class="bg-slate-900 border border-white/10 rounded-2xl p-6 max-w-lg w-full mx-4 relative z-10 shadow-2xl space-y-6 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-white/5 pb-4">
            <h3 class="text-lg font-bold text-white">Bulk Update Selected Employees</h3>
            <button onclick="closeBulkEditModal()" class="p-1.5 hover:bg-slate-800 rounded-lg text-slate-400 hover:text-white transition cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form id="bulk-edit-form" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Status -->
                <div>
                    <label for="bulk_status" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Account Status</label>
                    <select name="status" id="bulk_status" class="w-full bg-slate-950 border border-slate-800 rounded-xl py-2 px-3 text-slate-200 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <option value="">Keep unchanged</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <!-- Role -->
                <div>
                    <label for="bulk_role" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">System Role</label>
                    <select name="role" id="bulk_role" class="w-full bg-slate-950 border border-slate-800 rounded-xl py-2 px-3 text-slate-200 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <option value="">Keep unchanged</option>
                        <option value="Employee">Employee</option>
                        <option value="Manager">Manager</option>
                        <option value="Admin">Admin</option>
                    </select>
                </div>

                <!-- Department -->
                <div>
                    <label for="bulk_department_id" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Department</label>
                    <select name="department_id" id="bulk_department_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl py-2 px-3 text-slate-200 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <option value="">Keep unchanged</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->department_name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Designation -->
                <div>
                    <label for="bulk_designation_id" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Designation</label>
                    <select name="designation_id" id="bulk_designation_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl py-2 px-3 text-slate-200 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <option value="">Keep unchanged</option>
                        @foreach($designations as $desg)
                            <option value="{{ $desg->id }}">{{ $desg->designation_name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Location -->
                <div>
                    <label for="bulk_location_id" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Office Location</label>
                    <select name="location_id" id="bulk_location_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl py-2 px-3 text-slate-200 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <option value="">Keep unchanged</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->location_name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Shift -->
                <div>
                    <label for="bulk_shift_id" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Shift Schedule</label>
                    <select name="shift_id" id="bulk_shift_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl py-2 px-3 text-slate-200 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <option value="">Keep unchanged</option>
                        @foreach($shifts as $shf)
                            <option value="{{ $shf->id }}">{{ $shf->shift_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-white/5 pt-4">
                <button type="button" onclick="closeBulkEditModal()" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-medium text-xs transition cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-xs transition cursor-pointer">
                    Apply Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const selectAllCheckbox = document.getElementById('select-all');
        const rowCheckboxes = document.querySelectorAll('.row-checkbox');
        const bulkActionBar = document.getElementById('bulk-action-bar');
        const selectedCountSpan = document.getElementById('selected-count');

        function updateBulkActionBar() {
            const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
            if (checkedCount > 0) {
                selectedCountSpan.innerText = `${checkedCount} selected`;
                bulkActionBar.classList.remove('translate-y-24', 'opacity-0');
                bulkActionBar.classList.add('translate-y-0', 'opacity-100');
            } else {
                bulkActionBar.classList.remove('translate-y-0', 'opacity-100');
                bulkActionBar.classList.add('translate-y-24', 'opacity-0');
            }
        }

        selectAllCheckbox.addEventListener('change', () => {
            rowCheckboxes.forEach(cb => {
                cb.checked = selectAllCheckbox.checked;
            });
            updateBulkActionBar();
        });

        rowCheckboxes.forEach(cb => {
            cb.addEventListener('change', () => {
                const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
                selectAllCheckbox.checked = checkedCount === rowCheckboxes.length;
                updateBulkActionBar();
            });
        });

        // Bulk Delete Action
        window.confirmBulkDelete = async () => {
            const selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
            if (selectedIds.length === 0) return;

            if (confirm(`Are you sure you want to delete the ${selectedIds.length} selected employee(s)?`)) {
                try {
                    const response = await fetch('{{ route("employees.bulk_destroy") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ ids: selectedIds })
                    });
                    const res = await response.json();
                    if (response.ok) {
                        alert(res.message);
                        window.location.reload();
                    } else {
                        alert(res.message || 'Error occurred while bulk deleting.');
                    }
                } catch (err) {
                    alert('Network error. Failed to bulk delete.');
                }
            }
        };

        // Modal triggers
        window.openBulkEditModal = () => {
            document.getElementById('bulkEditModal').classList.remove('hidden');
        };

        window.closeBulkEditModal = () => {
            document.getElementById('bulkEditModal').classList.add('hidden');
        };

        // Form submit
        const bulkEditForm = document.getElementById('bulk-edit-form');
        bulkEditForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
            if (selectedIds.length === 0) return;

            const formData = new FormData(bulkEditForm);
            const data = { ids: selectedIds };
            formData.forEach((value, key) => {
                if (value) {
                    data[key] = value;
                }
            });

            try {
                const response = await fetch('{{ route("employees.bulk_update") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                const res = await response.json();
                if (response.ok) {
                    alert(res.message);
                    window.location.reload();
                } else {
                    alert(res.message || 'Error occurred while bulk updating.');
                }
            } catch (err) {
                alert('Network error. Failed to bulk update.');
            }
        });
    });
</script>
@endsection
