@extends('layouts.app')

@section('title', 'Employees')
@section('page_title', 'Employee Directory')

@section('content')
<div class="space-y-6">
    <!-- Top Action Banner -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md">
        <div>
            <h2 class="text-xl font-bold text-white">Manage Employees</h2>
            <p class="text-slate-400 text-sm mt-1">View, add, update, and search active or inactive employee files.</p>
        </div>
        <a href="{{ route('employees.create') }}" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm flex items-center gap-2 transition duration-200 shadow-lg shadow-indigo-500/25 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add Employee
        </a>
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
                        <tr class="hover:bg-white/[0.02] transition duration-150">
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
                            <td colspan="8" class="px-6 py-8 text-center text-slate-500">
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
@endsection
