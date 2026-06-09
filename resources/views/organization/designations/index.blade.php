@extends('layouts.app')

@section('title', 'Designations')
@section('page_title', 'Designation Management')

@section('content')
<div class="max-w-6xl mx-auto">

    <!-- Top Actions -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <p class="text-sm text-slate-400">Manage job roles, assign levels for reporting boundaries, and outline organization ranks.</p>
        </div>
        
        @can('designation.create')
        <a href="{{ route('designations.create') }}" class="px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-semibold rounded-xl text-xs shadow-lg shadow-indigo-500/25 transition duration-200 transform hover:-translate-y-0.5 cursor-pointer">
            + Add Designation
        </a>
        @endcan
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <!-- Search & Filters -->
    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-6 shadow-2xl mb-6">
        <form action="{{ route('designations.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
            <div class="flex-grow">
                <label for="search" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Search Designations</label>
                <input type="text" name="search" id="search" value="{{ $search }}" placeholder="Search by name or code..."
                    class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-2.5 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm">
            </div>

            <div class="w-full md:w-48 shrink-0">
                <label for="status" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Filter by Status</label>
                <select name="status" id="status" class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-2.5 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm">
                    <option value="all" {{ $status == 'all' ? 'selected' : '' }}>All Statuses</option>
                    <option value="active" {{ $status == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="flex gap-2 w-full md:w-auto">
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-xs transition duration-200 cursor-pointer w-full md:w-auto">
                    Apply
                </button>
                <a href="{{ route('designations.index') }}" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-white/5 font-semibold rounded-xl text-xs transition duration-200 cursor-pointer w-full md:w-auto text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl overflow-hidden shadow-2xl">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-white/5 bg-slate-900/30 text-slate-400 text-xs font-bold uppercase tracking-wider">
                    <th class="px-6 py-4">Designation Code</th>
                    <th class="px-6 py-4">Designation Name</th>
                    <th class="px-6 py-4">Hierarchy Level</th>
                    <th class="px-6 py-4">Reporting Parent</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5 text-sm text-slate-200">
                @forelse($designations as $desg)
                    <tr class="hover:bg-white/2 transition duration-150">
                        <td class="px-6 py-4 font-mono text-xs text-indigo-400 font-bold">
                            {{ $desg->designation_code }}
                        </td>
                        <td class="px-6 py-4 font-semibold">
                            {{ $desg->designation_name }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 bg-slate-800 text-slate-300 border border-white/5 text-xs rounded font-medium">
                                Level {{ $desg->level }} 
                                @if($desg->level == 1) (CEO / Top)
                                @elseif($desg->level == 2) (Director)
                                @elseif($desg->level == 3) (Manager)
                                @elseif($desg->level == 4) (Team Lead)
                                @else (Employee)
                                @endif
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($desg->hierarchy && $desg->hierarchy->parentDesignation)
                                <span class="text-xs text-slate-300">{{ $desg->hierarchy->parentDesignation->designation_name }}</span>
                            @else
                                <span class="text-xs text-slate-500">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg uppercase tracking-wider border {{ $desg->status == 'active' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/20' }}">
                                {{ $desg->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            @can('designation.edit')
                            <a href="{{ route('designations.edit', $desg->id) }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg text-xs font-semibold border border-white/5 transition duration-200 inline-block mr-2 cursor-pointer">
                                Edit
                            </a>
                            @endcan

                            @can('designation.delete')
                            <form action="{{ route('designations.destroy', $desg->id) }}" method="POST" class="inline-block" onsubmit="confirmDelete(event, this, 'Are you sure you want to delete this designation?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 rounded-lg text-xs font-semibold border border-rose-500/10 transition duration-200 cursor-pointer">
                                    Delete
                                </button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-slate-500">
                            No designations found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination links -->
    <div class="mt-6">
        {{ $designations->links() }}
    </div>

</div>
@endsection
