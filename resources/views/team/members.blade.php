@extends('layouts.app')

@section('title', 'Team Directory')
@section('page_title', 'My Team Members')

@section('content')
<div class="space-y-6">
    <!-- Action Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md">
        <div>
            <h2 class="text-xl font-bold text-white">Team Roster</h2>
            <p class="text-slate-400 text-sm mt-1">View employee profiles, reporting nodes, and locations for your direct reports.</p>
        </div>
        <a href="/team-members/structure" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm flex items-center gap-2 transition duration-200 shadow-lg shadow-indigo-500/25 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
            </svg>
            Reporting Structure
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md">
        <form action="/team-members" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div>
                <label for="search" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Search Team</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Name, code, email..." 
                       class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition">
            </div>

            <!-- Department Filter -->
            <div>
                <label for="department_id" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Department</label>
                <select name="department_id" id="department_id" 
                        class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 transition">
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
                        class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 transition">
                    <option value="">All Locations</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>
                            {{ $loc->location_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Apply button -->
            <div class="flex items-end">
                <button type="submit" class="w-full px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-medium text-sm transition duration-200 border border-slate-700 cursor-pointer">
                    Filter Roster
                </button>
            </div>
        </form>
    </div>

    <!-- Table -->
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
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($members as $emp)
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

                            <!-- Actions -->
                            <td class="px-6 py-4 text-right">
                                <a href="/team-members/{{ $emp->id }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-xl border border-slate-700 text-xs font-semibold transition" title="View details">
                                    View Profile
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                                No direct reports found matching the filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($members->hasPages())
            <div class="px-6 py-4 border-t border-white/10 bg-white/[0.01]">
                {{ $members->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
